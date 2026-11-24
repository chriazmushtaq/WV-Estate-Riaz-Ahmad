<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class FrontsController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
	

/**
 * Displays a view
 *
 * @param mixed What page to display
 * @return void
 * @throws NotFoundException When the view file could not be found
 *	or MissingViewException in debug mode.
 */
	public function display() {
		$path = func_get_args();

		$count = count($path);
		if (!$count) {
			return $this->redirect('/');
		}
		$page = $subpage = $title_for_layout = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title_for_layout'));
		
		$this->loadModel('Slide');
		 $slides=$this->Slide->find('all',array('conditions'=>array('status'=>'Active'),'order'=>array('ordering'=>'asc')));
		 $this->set('slides',$slides);
		$this->loadModel('Project');
		$allProject=$this->Project->find('all',array('order'=>array('Project.id'=>'desc'),'limit'=>6));
		$this->set('allProject',$allProject);
		$this->loadModel('Property');
		$allviewedProperty=$this->Property->find('all',array('conditions'=>array('Property.published'=>'Yes'),'order'=>array('Property.id'=>'desc'),'limit'=>16));
		$publishedPropertyCount=(int)$this->Property->find('count',array('conditions'=>array('Property.published'=>'Yes')));
		$allProperty=$this->Property->find('all',array('conditions'=>array('Property.featured'=>'Yes'),
							       'order'=>array('rand()'),'limit'=>10));
		$featuredTickerProperties=$this->Property->find('all',array(
		    'conditions'=>array('Property.featured'=>'Yes'),
		    'order'=>array('Property.id'=>'desc'),
		    'limit'=>20
		));
		if(empty($featuredTickerProperties))
		{
		    $featuredTickerProperties=$this->Property->find('all',array(
			'order'=>array('Property.id'=>'desc'),
			'limit'=>20
		    ));
		}
		$this->loadModel('Type');
		$this->Type->virtualFields= array('total_count' => 'Count(Property.id)');
		$propertyType=$this->Type->find('all',array('joins'=>array(array('table'=>'properties','alias'=>'Property','conditions'=>array('Type.id=Property.type_id'))),
							       'fields'=>array('Type.*','total_count'),
							       'group'=>array('Type.id')
							       ));
		$this->set('propertyType',$propertyType);
		$this->set('allProperty',$allProperty);
		$this->set('allviewedProperty',$allviewedProperty);
		$this->set('publishedPropertyCount',$publishedPropertyCount);
		$this->set('featuredTickerProperties',$featuredTickerProperties);
		$this->loadModel('Content');
		$contentValue=$this->Content->find('first',array('conditions'=>array('id'=>1,'published'=>'published')));
		$this->set('contentValue',$contentValue);
		
		// Safe Dynamic Frontend Theme Module Switching
		$isRealEstateThemeActive = false;
		if ($page === 'home' || empty($path) || (isset($path[0]) && $path[0] === 'home')) {
			try {
				if (CakePlugin::loaded('RealEstateTheme')) {
					$this->loadModel('SystemModule');
					$themeMod = $this->SystemModule->find('first', array(
						'conditions' => array('SystemModule.slug' => 'RealEstateTheme', 'SystemModule.status' => 1),
						'recursive' => -1
					));
					if (!empty($themeMod)) {
						$isRealEstateThemeActive = true;
					}
				}
			} catch (Exception $e) {
				$isRealEstateThemeActive = false;
			}
		}

		if ($isRealEstateThemeActive) {
			try {
				$this->loadModel('RealEstateTheme.RealEstateThemeSetting');
				$themeSettings = $this->RealEstateThemeSetting->find('first', array('conditions' => array('id' => 1)));
				$this->set('themeSettings', !empty($themeSettings['RealEstateThemeSetting']) ? $themeSettings['RealEstateThemeSetting'] : array());
				$this->layout = 'RealEstateTheme.real_estate_theme';
				$this->render('RealEstateTheme.Front/theme_home');
				return;
			} catch (Exception $e) {
				// Fallback seamlessly to standard default view
			}
		}

		try {
			$this->render(implode('/', $path));
		} catch (MissingViewException $e) {
			if (Configure::read('debug')) {
				throw $e;
			}
			throw new NotFoundException();
		}
	}

	public function download_brochure() {
		$this->autoRender = false;
		$this->response->type('json');
		
		if (!$this->request->is('post')) {
			$this->response->body(json_encode(array('status' => 'error', 'message' => __('Invalid request method.'))));
			return;
		}
		
		$data = $this->request->data;
		$name = isset($data['name']) ? trim((string)$data['name']) : '';
		$mobile = isset($data['mobile']) ? trim((string)$data['mobile']) : '';
		$email = isset($data['email']) ? trim((string)$data['email']) : '';
		$propertyId = isset($data['property_id']) ? (int)$data['property_id'] : 0;
		$remarks = isset($data['remarks']) ? trim((string)$data['remarks']) : '';
		$utmSource = isset($data['utm_source']) ? trim((string)$data['utm_source']) : '';
		$utmCampaign = isset($data['utm_campaign']) ? trim((string)$data['utm_campaign']) : '';
		$fbclid = isset($data['fbclid']) ? trim((string)$data['fbclid']) : '';
		
		if (empty($name) || empty($mobile)) {
			$this->response->body(json_encode(array('status' => 'error', 'message' => __('Please provide your Name and WhatsApp / Mobile number.'))));
			return;
		}
		
		$this->loadModel('Property');
		$property = $this->Property->find('first', array(
			'conditions' => array('Property.id' => $propertyId),
			'recursive' => 1
		));
		
		$propertyName = !empty($property['Property']['name']) ? $property['Property']['name'] : 'Property #' . $propertyId;
		$projectId = !empty($property['Property']['project_id']) ? (int)$property['Property']['project_id'] : 0;
		$agentId = !empty($property['Property']['user_id']) ? (int)$property['Property']['user_id'] : 1;
		
		// 1. Link or Create Client in clients table
		$this->loadModel('Client');
		$cleanMobile = preg_replace('/[^0-9\+]/', '', $mobile);
		$existingClient = $this->Client->find('first', array(
			'conditions' => array(
				'OR' => array(
					array('Client.mobile LIKE' => '%' . substr($cleanMobile, -9)),
					array('Client.phone LIKE' => '%' . substr($cleanMobile, -9))
				)
			),
			'recursive' => -1
		));
		
		$now = date('Y-m-d H:i:s');
		$today = date('Y-m-d');
		$clientId = null;
		
		if (!empty($existingClient['Client']['id'])) {
			$clientId = (int)$existingClient['Client']['id'];
		} else {
			$clientData = array(
				'Client' => array(
					'category_id' => 2,
					'user_id' => $agentId,
					'project_id' => $projectId > 0 ? $projectId : 0,
					'name' => $name,
					'mobile' => $mobile,
					'phone' => $mobile,
					'email' => !empty($email) ? $email : '',
					'status' => 'Active',
					'reg_status' => 'Live',
					'created' => $now,
					'modified' => $now
				)
			);
			$this->Client->create();
			if ($this->Client->save($clientData, false)) {
				$clientId = (int)$this->Client->id;
			}
		}
		
		// 2. Create Lead in leads table with Higly/Download status
		$this->loadModel('Admin.Lead');
		$this->loadModel('Status');
		$downloadStatus = $this->Status->find('first', array(
			'conditions' => array('OR' => array(
				array('Status.name LIKE' => '%Higly%'),
				array('Status.name LIKE' => '%Download%'),
				array('Status.type' => 'HDL')
			)),
			'recursive' => -1
		));
		$downloadStatusId = !empty($downloadStatus['Status']['id']) ? (int)$downloadStatus['Status']['id'] : 11;

		$leadDesc = sprintf(__('Downloaded Official Payment Plan PDF for %s (Mobile: %s, Email: %s)'), $propertyName, $mobile, $email);
		if (!empty($remarks)) {
			$leadDesc .= ' — Note: ' . $remarks;
		}
		
		$leadData = array(
			'Lead' => array(
				'client_id' => $clientId,
				'lead_name' => $name,
				'lead_mobile' => $mobile,
				'lead_email' => $email,
				'property_id' => $propertyId > 0 ? $propertyId : null,
				'project_id' => $projectId > 0 ? $projectId : 0,
				'user_id' => $agentId,
				'status_id' => $downloadStatusId, // Higly/Download
				'lead_source' => 'Payment Plan PDF Download',
				'lead_temperature' => 'hot',
				'remarks' => $leadDesc,
				'follow_up' => $now,
				'last_contact_date' => $today,
				'utm_source' => $utmSource,
				'utm_campaign' => $utmCampaign,
				'source_ad_id' => $fbclid,
				'created' => $now,
				'modified' => $now
			)
		);
		$this->Lead->create();
		$this->Lead->save($leadData, false);
		$leadId = $this->Lead->id;

		// 2. Check if a real uploaded PDF payment plan is linked to this property in backend
		$pdfFile = !empty($property['Property']['payment_plan_pdf']) ? trim((string)$property['Property']['payment_plan_pdf']) : '';
		$targetDir = WWW_ROOT . 'files' . DS . 'payment_plans';
		$hasRealFile = (!empty($pdfFile) && file_exists($targetDir . DS . $pdfFile) && is_file($targetDir . DS . $pdfFile));

		if ($hasRealFile) {
			$downloadUrl = Router::url('/files/payment_plans/' . $pdfFile, true);
			$cleanFileName = 'White_Villas_Payment_Plan_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $propertyName) . '.pdf';
			$message = sprintf(__('Thank you, %s! The official payment plan for %s is downloading.'), h($name), h($propertyName));
			
			$this->response->body(json_encode(array(
				'status' => 'success',
				'has_file' => true,
				'lead_id' => $leadId,
				'download_url' => $downloadUrl,
				'download_filename' => $cleanFileName,
				'property_name' => $propertyName,
				'message' => $message
			)));
		} else {
			$message = sprintf(__('Latest Payment Plan Not Available. Thank you, %s! Our sales team has received your request and will send the official updated payment plan for %s to your WhatsApp (%s) shortly.'), h($name), h($propertyName), h($mobile));
			
			$this->response->body(json_encode(array(
				'status' => 'success',
				'has_file' => false,
				'lead_id' => $leadId,
				'download_url' => '',
				'download_filename' => '',
				'property_name' => $propertyName,
				'message' => $message
			)));
		}
	}
}
