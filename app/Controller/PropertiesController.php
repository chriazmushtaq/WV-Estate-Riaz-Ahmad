<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeEmail', 'Network/Email');
ini_set('max_execution_time', 300);

class PropertiesController extends AppController
{
    public $helpers = array('Html', 'Form','Session','Paginator','Function','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg','CustomFunction');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Property.id'=>'desc'));

    protected function _propertySlug($value,$id=null)
    {
        $slug=trim(preg_replace('/[^A-Za-z0-9]+/','-',(string)$value),'-');
        if($slug==='')
        {
            $slug='property';
            if($id!==null && (int)$id>0)
            {
                $slug.='-'.(int)$id;
            }
        }
        return $slug;
    }

    protected function _propertySlugKey($value)
    {
        return strtolower($this->_propertySlug($value));
    }
    protected function _ensurePropertySurroundingsTable()
    {
        static $ready = null;
        if($ready !== null)
        {
            return $ready;
        }
        try
        {
            $this->Property->query("CREATE TABLE IF NOT EXISTS `property_surroundings` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `property_id` bigint(20) NOT NULL,
                `name` varchar(191) NOT NULL,
                `category` varchar(80) DEFAULT NULL,
                `distance` int(11) NOT NULL DEFAULT 0,
                `sort_order` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `property_id` (`property_id`),
                KEY `category` (`category`),
                KEY `sort_order` (`sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $ready = true;
        }
        catch(Exception $e)
        {
            $ready = false;
        }
        return $ready;
    }
    protected function _propertySurroundingsJson($propertyId, $fallbackJson='')
    {
        $propertyId = (int)$propertyId;
        $rows = array();
        if($propertyId>0 && $this->_ensurePropertySurroundingsTable())
        {
            try
            {
                $surroundings = (array)$this->Property->query("SELECT `name`,`category`,`distance` FROM `property_surroundings` WHERE `property_id`=".$propertyId." ORDER BY `sort_order` ASC, `id` ASC");
                foreach($surroundings as $row)
                {
                    $src = !empty($row['property_surroundings']) ? $row['property_surroundings'] : (!empty($row[0]) ? $row[0] : array());
                    if(!empty($src['name']))
                    {
                        $rows[] = array(
                            'name'=>(string)$src['name'],
                            'category'=>!empty($src['category']) ? (string)$src['category'] : __('Nearby'),
                            'distance'=>isset($src['distance']) ? (int)$src['distance'] : 0
                        );
                    }
                }
            }
            catch(Exception $e)
            {
                $rows = array();
            }
        }
        if(empty($rows) && $fallbackJson !== '')
        {
            $fallbackRows = json_decode((string)$fallbackJson, true);
            if(is_array($fallbackRows))
            {
                foreach($fallbackRows as $fallbackRow)
                {
                    if(is_array($fallbackRow) && !empty($fallbackRow['name']))
                    {
                        $rows[] = array(
                            'name'=>(string)$fallbackRow['name'],
                            'category'=>!empty($fallbackRow['category']) ? (string)$fallbackRow['category'] : __('Nearby'),
                            'distance'=>isset($fallbackRow['distance']) ? (int)$fallbackRow['distance'] : 0
                        );
                    }
                }
            }
        }
        return !empty($rows) ? json_encode($rows) : '[]';
    }

    protected function _findPublishedPropertyBySlug($slug)
    {
        $slug=trim(rawurldecode((string)$slug));
        if($slug==='')
        {
            return array();
        }
        if(preg_match('/^property\-([0-9]+)$/i',$slug,$match))
        {
            return $this->Property->findByIdAndPublished((int)$match[1],'Yes');
        }
        $post=$this->Property->find('first',array(
            'conditions'=>array(
                'Property.published'=>'Yes',
                'OR'=>array(
                    'Property.name'=>$slug,
                    'Property.uniq_id'=>$slug
                )
            )
        ));
        if($post)
        {
            return $post;
        }
        try
        {
            $this->loadModel('PropertySeo');
            $seoRow=$this->PropertySeo->find('first',array(
                'conditions'=>array('PropertySeo.slug'=>strtolower($this->_propertySlug($slug))),
                'recursive'=>-1
            ));
            if(!empty($seoRow['PropertySeo']['property_id']))
            {
                $seoPost=$this->Property->findByIdAndPublished((int)$seoRow['PropertySeo']['property_id'],'Yes');
                if($seoPost)
                {
                    return $seoPost;
                }
            }
        }
        catch(Exception $e)
        {
        }
        $slugKey=$this->_propertySlugKey($slug);
        $rows=$this->Property->find('all',array(
            'conditions'=>array('Property.published'=>'Yes'),
            'fields'=>array('Property.id','Property.name','Property.uniq_id'),
            'recursive'=>-1
        ));
        foreach((array)$rows as $row)
        {
            $name=isset($row['Property']['name']) ? $row['Property']['name'] : '';
            $uniqId=isset($row['Property']['uniq_id']) ? $row['Property']['uniq_id'] : '';
            if($this->_propertySlugKey($name)===$slugKey || ($uniqId!=='' && $this->_propertySlugKey($uniqId)===$slugKey))
            {
                return $this->Property->findByIdAndPublished((int)$row['Property']['id'],'Yes');
            }
        }
        return array();
    }

    public function index($id=null)
    {
        try
        {
            $this->loadModel('User');
            $this->loadModel('Type');
            $this->loadModel('Contract');
            $this->loadModel('Location');
            $this->loadModel('Project');
            $condition=array();$searchArr=array();
            $searchLink="";$notSearch=true;
            if(isset($this->params['named']['history']) && $this->params['named']['history']==1)
            {
                $notSearch=false;
            }
            if(isset($this->params['named']['type']) && strlen($this->params['named']['type'])>0)
            {
                $condition[]=array('Property.type_id'=>$this->params['named']['type']);
                $type=$this->Type->findById($this->params['named']['type']);
                $searchArr[]=array('type'=>$type['Type']['name']);
                $searchLink.='type:'.$this->params['named']['type'].'/';
            }
            if(isset($this->params['named']['contract']) && strlen($this->params['named']['contract'])>0)
            {
                $condition[]=array('Property.contract_id'=>$this->params['named']['contract']);
                $contract=$this->Contract->findById($this->params['named']['contract']);
                $searchArr[]=array('contract'=>$contract['Contract']['name']);
                $searchLink.='contract:'.$this->params['named']['contract'].'/';
            }
            if(isset($this->params['named']['location']) && strlen($this->params['named']['location'])>0)
            {
                $condition[]=array('Property.location_id'=>$this->params['named']['location']);
                $location=$this->Location->findById($this->params['named']['location']);
                $searchArr[]=array('location'=>$location['Location']['name']);
                $searchLink.='location:'.$this->params['named']['location'].'/';
            }
            if(isset($this->params['named']['keyword']) && strlen($this->params['named']['keyword'])>0)
            {
                $condition[]=array('OR'=>array(array('Property.'.'address'.' LIKE'=>"%".$this->params['named']['keyword']."%"),
                                               array('Property.'.'name'.' LIKE'=>"%".$this->params['named']['keyword']."%"),
                                               array('Property.'.'description'.' LIKE'=>"%".$this->params['named']['keyword']."%")));
                $searchArr[]=array('keyword'=>$this->params['named']['keyword']);
                $searchLink.='keyword:'.$this->params['named']['keyword'].'/';
            }
            if(isset($this->params['named']['start_price']) && strlen($this->params['named']['start_price'])>0 && isset($this->params['named']['end_price']) && strlen($this->params['named']['end_price'])>0)
            {
                $startDate=$this->params['named']['start_price'];
                $endDate=$this->params['named']['end_price'];
                $condition[]=array('Property.price BETWEEN ? AND ?'=>array($startDate,$endDate));
                $searchArr[]=array('start_price'=>$this->params['named']['start_price'],'end_price'=>$this->params['named']['end_price']);
                $searchLink.='start_price:'.$this->params['named']['start_price'].'/'.'end_price:'.$this->params['named']['end_price'].'/';
            }
            if(isset($this->params['named']['bedroom']) && strlen($this->params['named']['bedroom'])>0)
            {
                $condition[]=array('bedroom'=>$this->params['named']['bedroom']);
                $searchArr[]=array('bedroom'=>$this->params['named']['bedroom']);
                $searchLink.='bedroom:'.$this->params['named']['bedroom'].'/';
            }
            if(isset($this->params['named']['bathroom']) && strlen($this->params['named']['bathroom'])>0)
            {
                $condition[]=array('bathroom'=>$this->params['named']['bathroom']);
                $searchArr[]=array('bathroom'=>$this->params['named']['bathroom']);
                $searchLink.='bathroom:'.$this->params['named']['bathroom'].'/';
            }
            if(isset($this->params['named']['uniq_id']) && strlen($this->params['named']['uniq_id'])>0)
            {
                $condition[]=array('uniq_id'=>$this->params['named']['uniq_id']);
                $searchArr[]=array('uniq_id'=>$this->params['named']['uniq_id']);
                $searchLink.='uniq_id:'.$this->params['named']['uniq_id'].'/';
            }
            if(isset($this->params['named']['agent']) && strlen($this->params['named']['agent'])>0)
            {
                $condition[]=array('user_id'=>$this->params['named']['agent']);
                $agent=$this->User->findById($this->params['named']['agent']);
                $searchArr[]=array('agent'=>$agent['User']['name']);
                $searchLink.='agent:'.$this->params['named']['agent'].'/';
            }
            if(isset($this->params['named']['agency']) && strlen($this->params['named']['agency'])>0)
            {
                $condition[]=array('Property.project_id'=>$this->params['named']['agency']);
                $agency=$this->Project->findById($this->params['named']['agency']);
                $searchArr[]=array('agency'=>$agency['Project']['name']);
                $searchLink.='agency:'.$this->params['named']['agency'].'/';
            }
            $condition[]=array('Property.published'=>'Yes');
            $this->Prg->commonProcess();
            $this->Paginator->settings = $this->paginate;
            $this->Paginator->settings['conditions'] = array($this->Property->parseCriteria($this->Prg->parsedParams()),$condition);
            $this->Paginator->settings['limit']=$this->pageLimit;
            $this->Paginator->settings['maxLimit']=$this->maxLimit;
            $propertyRows = $this->Paginator->paginate();
            $this->set('property', $propertyRows);
            $this->set('type',$this->Type->find('list',array('order'=>array('Type.name'=>'asc'))));
            $this->set('contract',$this->Contract->find('list'));
            $this->set('location',$this->Location->find('list',array('order'=>array('Location.name'=>'asc'))));
            if(empty($propertyRows))
            {
                $searchRecord=0;
                if($notSearch) {
                    try
                    {
                        $email=$this->siteEmail;
                        $this->loadModel('Emailtemplate');
                        $emailArr=$this->Emailtemplate->findByType('PNF');
                        
                        if(!empty($emailArr['Emailtemplate']['status']) && $emailArr['Emailtemplate']['status']=="Published")
                        {
                            $message=$emailArr['Emailtemplate']['description'];
                            foreach($searchArr as $list) {
                                foreach($list as $key=>$value) {
                                    if($key=='type') {
                                        $message.='<br>'.__('Type').' = '.$value;
                                    }
                                    if($key=='contract') {
                                        $message.='<br>'.__('Contract').' = '.$value;
                                    }
                                    if($key=='location') {
                                        $message.='<br>'.__('Location').' = '.$value;
                                    }
                                    if($key=='keyword') {
                                        $message.='<br>'.__('Keyword').' = '.$value;
                                    }
                                    if($key=='start_price') {
                                        $message.='<br>'.__('Start Price').' = '.$value;
                                    }
                                    if($key=='end_price') {
                                        $message.='<br>'.__('End Price').' = '.$value;
                                    }
                                    if($key=='bedroom') {
                                        $message.='<br>'.__('Bedroom').' = '.$value;
                                    }
                                    if($key=='bathroom') {
                                        $message.='<br>'.__('Bathroom').' = '.$value;
                                    }
                                    if($key=='uniq_id') {
                                        $message.='<br>'.__('Property ID').' = '.$value;
                                    }
                                    if($key=='agent') {
                                        $message.='<br>'.__('Agent').' = '.$value;
                                    }
                                    if($key=='agency') {
                                        $message.='<br>'.__('Agency').' = '.$value;
                                    }
                                }
                            }
                            $Email = new CakeEmail();
                            $Email->transport($this->emailSettype);
                            if($this->emailSettype=="Smtp")
                            $Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword,'timeout'=>90));
                            $Email->from(array($this->siteEmail =>$this->siteName));
                            $Email->to($email);
                            $Email->template('default');
                            $Email->emailFormat('html');
                            $Email->subject($emailArr['Emailtemplate']['name']);
                            $Email->send($message);
                            /* End Email */
                        }
                    }
                    catch(Exception $e)
                    {
                    }
                }
            }
            else
            {
                $searchRecord=1;
            }
            $this->set('searchRecord',$searchRecord);
            $this->set('propertySearchArr',$searchArr);
            $this->set('propertySearchLink',$searchLink);
            if(strlen($searchLink)>0 && $notSearch) {
                $searchHistory=array('name'=>json_encode($searchArr),'link'=>$searchLink,'ip_address'=>$_SERVER['REMOTE_ADDR'],'search'=>$searchRecord);
                $this->loadModel('SearchHistory');
                $this->SearchHistory->create();
                $this->SearchHistory->save($searchHistory);
            }
            if ($this->request->is('ajax'))
            {
                $this->render('index','ajax'); // View, Layout
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function view($id=null)
    {
        $this->loadModel('User');
        $this->loadModel('PropertiesFeature');
        $this->loadModel('Deal');
        $this->loadModel('PropertySeo');
        $this->loadModel('PropertyRecentView');
        $this->loadModel('PropertyPriceHistory');
        $post = is_numeric($id) ? $this->Property->findByIdAndPublished($id,'Yes') : $this->_findPublishedPropertyBySlug($id);
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash', array('alert'=> 'danger'));
            return $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        $id=(int)$post['Property']['id'];
        $post['Property']['nearby_places_json'] = $this->_propertySurroundingsJson($id, !empty($post['Property']['nearby_places_json']) ? $post['Property']['nearby_places_json'] : '');
        if(!array_key_exists('nearby_places_json', $post['Property']))
        {
            try
            {
                $nearbyRow = $this->Property->query("SELECT `nearby_places_json` FROM `properties` WHERE `id`=".(int)$id." LIMIT 1");
                $post['Property']['nearby_places_json'] = !empty($nearbyRow[0]['properties']['nearby_places_json']) ? $nearbyRow[0]['properties']['nearby_places_json'] : '';
                if($post['Property']['nearby_places_json'] === '' && !empty($nearbyRow[0]['Property']['nearby_places_json']))
                {
                    $post['Property']['nearby_places_json'] = $nearbyRow[0]['Property']['nearby_places_json'];
                }
                if($post['Property']['nearby_places_json'] === '' && !empty($nearbyRow[0][0]['nearby_places_json']))
                {
                    $post['Property']['nearby_places_json'] = $nearbyRow[0][0]['nearby_places_json'];
                }
            }
            catch(Exception $e)
            {
                $post['Property']['nearby_places_json'] = '';
            }
        }
        $views=$post['Property']['views']+1;
        $this->Property->save(array('id'=>$id,'views'=>$views));
        $seo = $this->PropertySeo->find('first',array('conditions'=>array('PropertySeo.property_id'=>$id),'recursive'=>-1));
        if(!empty($seo['PropertySeo']['meta_title']))
        {
            $this->set('siteTitle',$seo['PropertySeo']['meta_title']);
        }
        if(!empty($seo['PropertySeo']['meta_description']))
        {
            $this->set('siteDescription',$seo['PropertySeo']['meta_description']);
        }
        $publicDocuments=array();
        foreach((array)$post['PropertiesDocument'] as $documentRow)
        {
            if(!array_key_exists('public_visible',$documentRow) || !empty($documentRow['public_visible']))
            {
                $publicDocuments[]=$documentRow;
            }
        }
        $post['PropertiesDocument']=$publicDocuments;
        $clientId = null;
        try
        {
            $frontUser=(array)$this->Session->read('frontUser');
            $clientId=!empty($frontUser['Client']['id']) ? (int)$frontUser['Client']['id'] : null;
            $sessionId=session_id();
            $recentConditions=array('PropertyRecentView.property_id'=>$id);
            if($clientId)
            {
                $recentConditions['PropertyRecentView.client_id']=$clientId;
            }
            else
            {
                $recentConditions['PropertyRecentView.session_id']=$sessionId;
            }
            $recent=$this->PropertyRecentView->find('first',array('conditions'=>$recentConditions,'recursive'=>-1));
            $recentSave=array(
                'property_id'=>$id,
                'client_id'=>$clientId,
                'session_id'=>$sessionId,
                'visitor_ip'=>isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'created'=>$this->currentDateTime
            );
            if(!empty($recent['PropertyRecentView']['id']))
            {
                $recentSave['id']=(int)$recent['PropertyRecentView']['id'];
            }
            $this->PropertyRecentView->create();
            $this->PropertyRecentView->save(array('PropertyRecentView'=>$recentSave),false);
        }
        catch(Exception $e)
        {
        }
        $feature = $this->PropertiesFeature->find('all',array('joins'=>array(array('table'=>'features','alias'=>'Feature','conditions'=>array('Feature.id=PropertiesFeature.feature_id'))),
                                                              'conditions'=>array('PropertiesFeature.property_id'=>$id),
                                                              'fields'=>array('Feature.name')
                                                             ));
        $similarProperties=$this->Property->find('all',array(
            'conditions'=>array(
                'Property.published'=>'Yes',
                'Property.id !='=>$id
            ),
            'limit'=>6,
            'order'=>array('Property.views'=>'desc','Property.id'=>'desc')
        ));
        $baseSimilarConditions=array(
            'Property.published'=>'Yes',
            'Property.id !='=>$id
        );
        if(!empty($post['Property']['location_id']))
        {
            $baseSimilarConditions['Property.location_id']=$post['Property']['location_id'];
        }
        if(!empty($post['Property']['unit_id']))
        {
            $baseSimilarConditions['Property.unit_id']=$post['Property']['unit_id'];
        }
        $price=(float)$post['Property']['price'];
        $areaRaw=isset($post['Property']['area']) ? $post['Property']['area'] : null;
        $primaryConditions=$baseSimilarConditions;
        if($price>0)
        {
            $primaryConditions['Property.price >=']=round($price*0.80,2);
            $primaryConditions['Property.price <=']=round($price*1.20,2);
        }
        if(is_numeric($areaRaw) && (float)$areaRaw>0)
        {
            $area=(float)$areaRaw;
            $primaryConditions['Property.area >=']=round($area*0.85,2);
            $primaryConditions['Property.area <=']=round($area*1.15,2);
        }
        elseif(!empty($areaRaw))
        {
            $primaryConditions['Property.area']=$areaRaw;
        }
        if(empty($similarProperties))
        {
            $similarProperties=$this->Property->find('all',array(
                'conditions'=>$primaryConditions,
                'limit'=>6,
                'order'=>array('Property.id'=>'desc')
            ));
        }
        $projectId=isset($post['Property']['project_id']) ? (int)$post['Property']['project_id'] : 0;
        $totalSoldDeals=0;
        if($projectId>0)
        {
            $totalSoldDeals=(int)$this->Deal->find('count',array(
                'joins'=>array(
                    array(
                        'table'=>'properties',
                        'alias'=>'ProjectProperty',
                        'type'=>'INNER',
                        'conditions'=>array('Deal.property_id = ProjectProperty.id')
                    )
                ),
                'conditions'=>array('ProjectProperty.project_id'=>$projectId),
                'recursive'=>-1
            ));
        }
        $verificationBadges=array(
            'approved'=>(isset($post['Property']['published']) && $post['Property']['published']==='Yes'),
            'documents_verified'=>!empty($post['PropertiesDocument']),
            'possession_status'=>(isset($post['Property']['status']) && strtolower((string)$post['Property']['status'])==='sold') ? __('Possession Completed') : __('Available for Possession')
        );
        $agencyTrust=array(
            'name'=>!empty($post['Project']['name']) ? $post['Project']['name'] : __('Not Specified'),
            'phone'=>!empty($post['Project']['phone']) ? $post['Project']['phone'] : __('Not Specified'),
            'mobile'=>!empty($post['Project']['mobile']) ? $post['Project']['mobile'] : __('Not Specified'),
            'email'=>!empty($post['Project']['email']) ? $post['Project']['email'] : __('Not Specified'),
            'response_time'=>__('Within 24 Hours'),
            'total_sold_deals'=>$totalSoldDeals
        );
        $mapProperties=$this->Property->find('all',array(
            'conditions'=>array(
                'Property.published'=>'Yes',
                'Property.latitude IS NOT NULL',
                'Property.longitude IS NOT NULL',
                "Property.latitude <> ''",
                "Property.longitude <> ''",
                "Property.latitude <> '0'",
                "Property.longitude <> '0'"
            ),
            'limit'=>500,
            'order'=>array('Property.id'=>'desc')
        ));
        $priceHistory=$this->PropertyPriceHistory->find('all',array(
            'conditions'=>array('PropertyPriceHistory.property_id'=>$id,'PropertyPriceHistory.old_price IS NOT NULL'),
            'order'=>array('PropertyPriceHistory.created'=>'desc','PropertyPriceHistory.id'=>'desc'),
            'limit'=>6,
            'recursive'=>-1
        ));
        $recentConditions=array('PropertyRecentView.property_id !='=>$id);
        if(!empty($clientId))
        {
            $recentConditions['PropertyRecentView.client_id']=$clientId;
        }
        else
        {
            $recentConditions['PropertyRecentView.session_id']=session_id();
        }
        $recentViewRows=$this->PropertyRecentView->find('all',array(
            'fields'=>array('PropertyRecentView.property_id','MAX(PropertyRecentView.created) AS last_seen'),
            'conditions'=>$recentConditions,
            'group'=>array('PropertyRecentView.property_id'),
            'order'=>array('last_seen'=>'desc'),
            'limit'=>4,
            'recursive'=>-1
        ));
        $recentPropertyIds=array();
        foreach((array)$recentViewRows as $recentViewRow)
        {
            if(!empty($recentViewRow['PropertyRecentView']['property_id']))
            {
                $recentPropertyIds[]=(int)$recentViewRow['PropertyRecentView']['property_id'];
            }
        }
        $recentlyViewedProperties=array();
        if(!empty($recentPropertyIds))
        {
            $recentlyViewedProperties=$this->Property->find('all',array(
                'conditions'=>array('Property.published'=>'Yes','Property.id'=>$recentPropertyIds),
                'limit'=>4
            ));
        }
        $this->set('post',$post);
        $this->set('propertySeo',$seo);
        $this->set('priceHistory',$priceHistory);
        $this->set('recentlyViewedProperties',$recentlyViewedProperties);
        $this->set('feature',$feature);
        $this->set('similarProperties',$similarProperties);
        $this->set('mapProperties',$mapProperties);
        $this->set('verificationBadges',$verificationBadges);
        $this->set('agencyTrust',$agencyTrust);
        $this->set('userArr',$this->User->findById($post['Property']['user_id']));
        $isRating=CakeSession::read('rating');
        $this->set('isRating',$isRating);
    }
    public function printproperty($id)
    {
        $this->layout=null;
        $this->view($id);
        $this->loadModel('Template');
        $templateArr=$this->Template->findByTypeAndStatus('PRO','Published');
        if($templateArr['Template']['status']=="Published")
        { 
           $this->set('template',$templateArr['Template']['description']);
        }
    }
    public function calculator($amount)
    {
        $this->layout=null;
        $this->set('amount',$amount);
        $frequencyArr=array('12'=>__('Monthly'),'4'=>__('Quarterly'),'26'=>__('Fortnightly'),'52'=>__('Weekly'));
        $loanTypeArr=array('pi'=>__('Principal & Interest'),'io'=>__('Interest only'));
        $this->set('frequencyArr',$frequencyArr);
        $this->set('loanTypeArr',$loanTypeArr);
    }
    public function favourite($id,$status)
    {
        $this->layout=null;
        $this->autoRender=false;
        $favUrl=Router::url(['controller' => 'Properties', 'action' => 'favourite']);
        return $this->CustomFunction->favouriteProperty($favUrl,$id,$status);
    }
    public function compare($id,$status)
    {
        $this->layout=null;
        $this->autoRender=false;
        $compUrl=Router::url(['controller' => 'Properties', 'action' => 'compare']);
        $imgUrl=Router::url(['controller' => 'img']);
        return $this->CustomFunction->compareProperty($compUrl,$imgUrl,$id,$status);
    }
    public function inquiry($id=null)
    {
        try{
            $this->loadModel('Lead');
            $this->loadModel('Agent');
            $this->loadModel('Property');
            $this->loadModel('Project');
            $property=$this->Property->find('first',array('conditions'=>array('Property.id'=>$id)));
            if(!$property)
            {
                $this->Session->setFlash(__('Invalid Property'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            if($this->request->is(array('post', 'put'))) {
                $clientName=trim(isset($this->request->data['full_name']) ? $this->request->data['full_name'] : '');
                $mobileNo=trim(isset($this->request->data['mobile']) ? $this->request->data['mobile'] : '');
                $email=trim(isset($this->request->data['email']) ? $this->request->data['email'] : '');
                $subject=trim(isset($this->request->data['subject']) ? $this->request->data['subject'] : '');
                $messageText=trim(isset($this->request->data['message']) ? $this->request->data['message'] : '');
                if($clientName=='' || $mobileNo=='')
                {
                    $this->Session->setFlash(__('Please enter your name and mobile number.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action' => 'view',$id));
                }
                $assignedUserId=!empty($property['Property']['user_id']) ? $property['Property']['user_id'] : 1;
                $projectId=!empty($property['Property']['project_id']) ? $property['Property']['project_id'] : null;
                $propertyName=!empty($property['Property']['name']) ? $property['Property']['name'] : '';
                $propertyOfficeId=!empty($property['Property']['uniq_id']) ? $property['Property']['uniq_id'] : '';
                $propertyUrl=Router::url('/Properties/'.$this->_propertySlug($propertyName,$id),true);
                $leadRemarks="Property Inquiry\n";
                $leadRemarks.="Property ID: ".$id."\n";
                if($propertyOfficeId!='')$leadRemarks.="Office ID: ".$propertyOfficeId."\n";
                if($propertyName!='')$leadRemarks.="Property: ".$propertyName."\n";
                if($subject!='')$leadRemarks.="Subject: ".$subject."\n";
                if($messageText!='')$leadRemarks.="Message: ".$messageText;
                $lead=array(
                    'user_id'=>$assignedUserId,
                    'project_id'=>$projectId,
                    'status_id'=>1,
                    'client_id'=>null,
                    'property_id'=>$id,
                    'remarks'=>$leadRemarks,
                    'lead_name'=>$clientName,
                    'lead_mobile'=>$mobileNo,
                    'lead_email'=>$email,
                    'lead_source'=>'Web Inquiry',
                    'last_contact_date'=>date('Y-m-d'),
                    'follow_up'=>$this->currentDateTime
                );
                $this->Lead->create();
                $this->Lead->save($lead,false);
                $agent=array();
                if(!empty($property['Property']['user_id']))
                {
                    $agent=$this->Agent->find('first',array('conditions'=>array('Agent.id'=>$property['Property']['user_id'])));
                }
                $project=array();
                if(!empty($property['Property']['project_id']))
                {
                    $project=$this->Project->find('first',array('conditions'=>array('Project.id'=>$property['Property']['project_id'])));
                }
                $recipientEmails=array();
                if(!empty($agent['Agent']['email']))$recipientEmails[]=$agent['Agent']['email'];
                if(!empty($project['Project']['email']))$recipientEmails[]=$project['Project']['email'];
                if(!empty($this->siteEmail))$recipientEmails[]=$this->siteEmail;
                $recipientEmails=array_unique(array_filter($recipientEmails));
                if(!empty($recipientEmails))
                {
                    $mailSubject=($subject!='' ? $subject : 'New Property Inquiry').' - Property #'.$id;
                    $mailMessage='<h3>New Property Inquiry</h3>';
                    $mailMessage.='<p><strong>Property ID:</strong> '.h($id).'</p>';
                    if($propertyOfficeId!='')$mailMessage.='<p><strong>Office ID:</strong> '.h($propertyOfficeId).'</p>';
                    if($propertyName!='')$mailMessage.='<p><strong>Property:</strong> '.h($propertyName).'</p>';
                    $mailMessage.='<p><strong>Property Link:</strong> <a href="'.h($propertyUrl).'">'.h($propertyUrl).'</a></p>';
                    $mailMessage.='<hr>';
                    $mailMessage.='<p><strong>Name:</strong> '.h($clientName).'</p>';
                    $mailMessage.='<p><strong>Mobile:</strong> '.h($mobileNo).'</p>';
                    if($email!='')$mailMessage.='<p><strong>Email:</strong> '.h($email).'</p>';
                    if($subject!='')$mailMessage.='<p><strong>Subject:</strong> '.h($subject).'</p>';
                    $mailMessage.='<p><strong>Message:</strong><br>'.nl2br(h($messageText)).'</p>';
                    $mailMessage.='<p><strong>Submitted On:</strong> '.h($this->currentDateTime).'</p>';
                    try
                    {
                        $Email = new CakeEmail();
                        $Email->transport($this->emailSettype);
                        if($this->emailSettype=="Smtp")
                        $Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword,'timeout'=>90));
                        $Email->from(array($this->siteEmail =>$this->siteName));
                        $Email->to(array_values($recipientEmails));
                        $Email->template('default');
                        $Email->emailFormat('html');
                        $Email->subject($mailSubject);
                        $Email->send($mailMessage);
                    }
                    catch(Exception $emailException)
                    {
                        $this->log('Property inquiry email failed for property '.$id.': '.$emailException->getMessage(),'error');
                    }
                }
                if($this->smsNotification)
                {
                    $smsProperty=($propertyOfficeId!='') ? $propertyOfficeId : '#'.$id;
                    $userSms='Dear '.$clientName.', your inquiry for property '.$smsProperty.' has been received. '.$this->siteName.' will contact you soon.';
                    $this->CustomFunction->sendSms($mobileNo,$userSms,$this->smsSettingArr);
                    $adminSms='New property inquiry '.$smsProperty.'. Client: '.$clientName.', Mobile: '.$mobileNo;
                    $smsNumbers=array();
                    if(!empty($agent['Agent']['mobile']))$smsNumbers[]=$agent['Agent']['mobile'];
                    if(!empty($agent['Agent']['phone']))$smsNumbers[]=$agent['Agent']['phone'];
                    if(!empty($project['Project']['mobile']))$smsNumbers[]=$project['Project']['mobile'];
                    if(!empty($project['Project']['phone']))$smsNumbers[]=$project['Project']['phone'];
                    if(!empty($this->contact))$smsNumbers[]=$this->contact;
                    $smsNumbers=array_unique(array_filter($smsNumbers));
                    foreach($smsNumbers as $smsNumber)
                    {
                        $this->CustomFunction->sendSms($smsNumber,$adminSms,$this->smsSettingArr);
                    }
                }
                $this->Session->setFlash(__('Inquiry successfully sent'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'view',$id));
            }
        }
        catch(Exception $e)
        {
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view',$id));
        }
    }
    public function trackcontact($id=null,$type=null)
    {
        $this->autoRender=false;
        $id=(int)$id;
        $type=strtolower(trim((string)$type));
        $allowed=array('call','whatsapp','email','share','print','document','inquiry');
        if($id<=0 || !in_array($type,$allowed,true))
        {
            return '';
        }
        try
        {
            $property=$this->Property->find('first',array('conditions'=>array('Property.id'=>$id,'Property.published'=>'Yes'),'recursive'=>-1));
            if(empty($property))
            {
                return '';
            }
            $this->loadModel('PropertyLeadClick');
            $frontUser=(array)$this->Session->read('frontUser');
            $this->PropertyLeadClick->create();
            $this->PropertyLeadClick->save(array('PropertyLeadClick'=>array(
                'property_id'=>$id,
                'project_id'=>!empty($property['Property']['project_id']) ? (int)$property['Property']['project_id'] : null,
                'agent_id'=>!empty($property['Property']['user_id']) ? (int)$property['Property']['user_id'] : null,
                'client_id'=>!empty($frontUser['Client']['id']) ? (int)$frontUser['Client']['id'] : null,
                'click_type'=>$type,
                'visitor_ip'=>isset($_SERVER['REMOTE_ADDR']) ? substr((string)$_SERVER['REMOTE_ADDR'],0,64) : '',
                'user_agent'=>isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'],0,255) : '',
                'referrer'=>isset($_SERVER['HTTP_REFERER']) ? substr((string)$_SERVER['HTTP_REFERER'],0,255) : '',
                'created'=>$this->currentDateTime
            )),false);
        }
        catch(Exception $e)
        {
        }
        return '';
    }
    public function savesearch()
    {
        if(!$this->request->is('post'))
        {
            return $this->redirect(array('action'=>'index'));
        }
        try
        {
            $this->loadModel('PropertySavedSearch');
            $data=!empty($this->request->data['PropertySavedSearch']) ? $this->request->data['PropertySavedSearch'] : array();
            if(empty($data))
            {
                foreach(array('name','email','criteria_json') as $field)
                {
                    if(isset($this->request->data[$field]))
                    {
                        $data[$field]=$this->request->data[$field];
                    }
                    elseif(isset($_POST['data']['PropertySavedSearch'][$field]))
                    {
                        $data[$field]=$_POST['data']['PropertySavedSearch'][$field];
                    }
                    else
                    {
                        $rawKey='data[PropertySavedSearch]['.$field.']';
                        if(isset($_POST[$rawKey]))
                        {
                            $data[$field]=$_POST[$rawKey];
                        }
                    }
                }
            }
            $email=trim((string)(isset($data['email']) ? $data['email'] : ''));
            $frontUser=(array)$this->Session->read('frontUser');
            if($email==='' && !empty($frontUser['Client']['email']))
            {
                $email=$frontUser['Client']['email'];
            }
            if($email==='' || !filter_var($email,FILTER_VALIDATE_EMAIL))
            {
                $this->Session->setFlash(__('Please enter a valid email to save this search.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action'=>'index'));
            }
            $criteria=array();
            if(!empty($data['criteria_json']))
            {
                $criteriaDecoded=json_decode($data['criteria_json'],true);
                if(is_array($criteriaDecoded))
                {
                    $criteria=$criteriaDecoded;
                }
            }
            $this->PropertySavedSearch->create();
            $this->PropertySavedSearch->save(array('PropertySavedSearch'=>array(
                'client_id'=>!empty($frontUser['Client']['id']) ? (int)$frontUser['Client']['id'] : null,
                'name'=>trim((string)(isset($data['name']) ? $data['name'] : __('Property Search'))),
                'email'=>$email,
                'criteria_json'=>json_encode($criteria),
                'alert_frequency'=>'instant',
                'status'=>'Active'
            )),false);
            $this->Session->setFlash(__('Search saved. Our team can use this requirement for matching properties.'),'flash',array('alert'=>'success'));
        }
        catch(Exception $e)
        {
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index'));
    }
    
    public function sendbymail($id = null)
    {        
        $this->loadModel('Property');
        $this->loadModel('PropertiesFeature');
        $post = $this->Property->findById($id);
        $feature = $this->PropertiesFeature->find('all',array('joins'=>array(array('table'=>'features','alias'=>'Feature','conditions'=>array('Feature.id=PropertiesFeature.feature_id'))),
                                                              'conditions'=>array('PropertiesFeature.property_id'=>$id),
                                                              'fields'=>array('Feature.name')
                                                             ));
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash', array('alert'=> 'danger'));
            $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        if ($this->request->is(array('post', 'put')))
        {
            try
            {
                $email=$this->request->data['Property']['email'];
                $allMails=explode(",",$email);
                $this->loadModel('Template');
                $templateArr=$this->Template->findByTypeAndStatus('EMA','Published');
                if($templateArr['Template']['status']=="Published")
                {
                    $feature1=array();
                    $photoUrl=$this->siteDomain.'/img/nia.png';
                    foreach($post['PropertiesPhoto'] as $post1):
                    if(empty($post1['photo']))continue;
                    $photoUrl=$this->siteDomain.'/img/'.$post1['dir'].'/'.$post1['photo'];
                    if(!empty($post1['home']) && $post1['home']=='Yes')break;
                    unset($post1);endforeach;
                    foreach($feature as $fpost):
                    $feature1[]=$fpost['Feature']['name'];
                    unset($fpost);endforeach;
                    $feature=implode(",",$feature1);
                    $propertyPhoto='<img src="'.$photoUrl.'">';
                    $propertyName=$post['Property']['name'];
                    $locationName=$post['Location']['name'];
                    $contractName=$post['Contract']['name'];
                    $agencyName=$post['Project']['name'];
                    $propertyPrice=$this->currencyFull.' '.$post['Property']['price'];
                    $propertyStatus=$post['Property']['status'];
                    $propertyBedroom=$post['Property']['bedroom'];
                    $propertyBathroom=$post['Property']['bathroom'];
                    $propertyArea=$post['Property']['area'].' '.$post['Unit']['name'];
                    $propertyView=$post['Property']['views'];
                    $propertyDescription=$post['Property']['description'];
                    $propertyAgent=$post['User']['name'];
                    $templateValue=$this->_renderTemplateString((string)$templateArr['Template']['description'], get_defined_vars());
                    foreach($allMails as $mailValue)
                    {  
                        $Email = new CakeEmail();
                        $Email->transport($this->emailSettype);
                        if($this->emailSettype=="Smtp")
                        $Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword,'timeout'=>90));
                        $Email->from(array($this->siteEmail =>$this->siteName));
                        $Email->to($mailValue);
                        $Email->viewVars(array('template'=>$templateValue));
                        $Email->template('mailproperty','default');
                        $Email->emailFormat('html');
                        $Email->subject($templateArr['Template']['name']);
                        $Email->send();
                    }
                    $this->Session->setFlash(__('E-Mail Successfully sent'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'view/',$id));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'view/',$id));
            }
            $this->set('isError',true);
        }
        else
        {
            $this->layout = null;
            $this->set('isError',false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }
    public function rating($id)
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        $post=$this->Property->findById($id);
        if($post)
        {
            $ratingUser=$post['Property']['rating_users']+1;
            $ratingCount=$post['Property']['rating_count']+$this->request->data['Property']['rating'];
            $rating=round($ratingCount/$ratingUser,1);
            $recordArr=array('id'=>$id,'rating_users'=>$ratingUser,'rating_count'=>$ratingCount,'rating'=>$rating);
            $this->Property->save($recordArr);
            CakeSession::write('rating',true);
            $startRating=$this->CustomFunction->starRating($rating);
            print'<div class="comment-rating" data-score="'.$rating.'" title="'.__('Rating').' '.$rating.'">'.$startRating.'</div><span>'.$ratingUser.' '.__('Ratings').'</span>';
            die();
        }        
    }
}
