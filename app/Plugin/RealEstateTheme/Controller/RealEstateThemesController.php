<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class RealEstateThemesController extends AdminAppController {
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'MenuBuilder.MenuBuilder', 'Js');
    public $components = array('Session', 'search-master.Prg', 'Paginator');
    public $uses = array('RealEstateTheme.RealEstateThemeSetting', 'SystemModule');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'Admin.default';
        if (!isset($this->request->params['pass']) || !is_array($this->request->params['pass'])) {
            $this->request->params['pass'] = array();
        }
        if (!isset($this->request->params['named']) || !is_array($this->request->params['named'])) {
            $this->request->params['named'] = array();
        }
        $this->set('allowViewAction', true);
        $this->set('allowAddAction', true);
        $this->set('allowEditAction', true);
        $this->set('allowDeleteAction', true);
        $this->set('isSuperAdminSession', true);
        
        $sessionUser = (array)$this->Session->read('User');
        if (!$this->_isSuperAdminSession($sessionUser) && !$this->_isCeoRoleContext($sessionUser)) {
            $this->Session->setFlash(__('Only Super Admin can access Theme Options.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
            return;
        }
    }

    public function admin_index() {
        $this->index();
        if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
            return;
        }
        return $this->render('RealEstateTheme.RealEstateThemes/index');
    }

    public function admin_settings() {
        $this->settings();
        if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
            return;
        }
        return $this->render('RealEstateTheme.RealEstateThemes/settings');
    }

    /**
     * Theme Overview Dashboard
     */
    public function index() {
        $this->layout = 'Admin.default';
        $this->set('title_for_layout', __('Real Estate Pro Theme Overview'));
        $this->set('allowViewAction', true);
        $this->set('allowAddAction', true);
        $this->set('allowEditAction', true);
        $this->set('allowDeleteAction', true);
        $this->set('isSuperAdminSession', true);

        $themeSetting = $this->RealEstateThemeSetting->getSettings();
        $module = $this->SystemModule->find('first', array('conditions' => array('SystemModule.slug' => 'RealEstateTheme'), 'recursive' => -1));
        $isActive = !empty($module['SystemModule']['status']);

        $this->set(compact('themeSetting', 'isActive', 'module'));
    }

    /**
     * Theme Options & Settings Manager
     */
    public function settings() {
        $this->layout = 'Admin.default';
        $this->set('title_for_layout', __('Theme Options & Customizer'));
        $this->set('allowViewAction', true);
        $this->set('allowAddAction', true);
        $this->set('allowEditAction', true);
        $this->set('allowDeleteAction', true);
        $this->set('isSuperAdminSession', true);

        $themeSetting = $this->RealEstateThemeSetting->getSettings();
        $id = !empty($themeSetting['id']) ? (int)$themeSetting['id'] : 1;

        if ($this->request->is(array('post', 'put'))) {
            $data = !empty($this->request->data['RealEstateThemeSetting']) ? $this->request->data['RealEstateThemeSetting'] : array();
            $data['id'] = $id;
            $data['updated'] = date('Y-m-d H:i:s');
            
            // Ensure boolean toggles are parsed
            $data['top_header_enabled'] = !empty($data['top_header_enabled']) ? 1 : 0;
            $data['sticky_header_enabled'] = !empty($data['sticky_header_enabled']) ? 1 : 0;
            $data['sticky_header_mobile_enabled'] = !empty($data['sticky_header_mobile_enabled']) ? 1 : 0;
            $data['header_book_btn_new_tab'] = !empty($data['header_book_btn_new_tab']) ? 1 : 0;
            $data['hero_audio_btn_enabled'] = !empty($data['hero_audio_btn_enabled']) ? 1 : 0;
            $data['hero_section_enabled'] = !empty($data['hero_section_enabled']) ? 1 : 0;
            $data['intro_section_enabled'] = !empty($data['intro_section_enabled']) ? 1 : 0;
            $data['properties_section_enabled'] = !empty($data['properties_section_enabled']) ? 1 : 0;
            $data['reasons_section_enabled'] = !empty($data['reasons_section_enabled']) ? 1 : 0;
            $data['area_section_enabled'] = !empty($data['area_section_enabled']) ? 1 : 0;
            $data['other_section_enabled'] = !empty($data['other_section_enabled']) ? 1 : 0;
            $data['amenities_enabled'] = !empty($data['amenities_enabled']) ? 1 : 0;
            $data['ticker_enabled'] = !empty($data['ticker_enabled']) ? 1 : 0;
            $data['ticker_show_on_all_pages'] = !empty($data['ticker_show_on_all_pages']) ? 1 : 0;
            $data['ticker_mobile_hide_badge'] = !empty($data['ticker_mobile_hide_badge']) ? 1 : 0;
            $data['ticker_mobile_enabled'] = !empty($data['ticker_mobile_enabled']) ? 1 : 0;
            if (isset($data['ticker_speed'])) {
                $data['ticker_speed'] = max(5, min(120, (int)$data['ticker_speed']));
            }
            $data['contact_section_enabled'] = !empty($data['contact_section_enabled']) ? 1 : 0;
            $data['newsletter_section_enabled'] = !empty($data['newsletter_section_enabled']) ? 1 : 0;
            $data['btn_pdf_pc_enabled'] = !empty($data['btn_pdf_pc_enabled']) ? 1 : 0;
            $data['btn_pdf_mobile_enabled'] = !empty($data['btn_pdf_mobile_enabled']) ? 1 : 0;
            $data['btn_whatsapp_pc_enabled'] = !empty($data['btn_whatsapp_pc_enabled']) ? 1 : 0;
            $data['btn_whatsapp_mobile_enabled'] = !empty($data['btn_whatsapp_mobile_enabled']) ? 1 : 0;
            $data['btn_call_pc_enabled'] = !empty($data['btn_call_pc_enabled']) ? 1 : 0;
            $data['btn_call_mobile_enabled'] = !empty($data['btn_call_mobile_enabled']) ? 1 : 0;
            $data['card_agent_pc_enabled'] = !empty($data['card_agent_pc_enabled']) ? 1 : 0;
            $data['card_agent_mobile_enabled'] = !empty($data['card_agent_mobile_enabled']) ? 1 : 0;
            if (isset($data['properties_count'])) {
                $data['properties_count'] = max(1, min(30, (int)$data['properties_count']));
            }
            if (isset($data['sections_order'])) {
                $data['sections_order'] = implode(',', $this->RealEstateThemeSetting->parseSectionsOrder($data['sections_order']));
            }

            // Handle Amenities List
            if (isset($data['amenities_items']) && is_array($data['amenities_items'])) {
                $cleanAmenities = array();
                $itemIndex = 1;
                foreach ($data['amenities_items'] as $item) {
                    if (empty($item['title'])) continue;
                    $cleanAmenities[] = array(
                        'id' => $itemIndex++,
                        'title' => trim((string)$item['title']),
                        'description' => isset($item['description']) ? trim((string)$item['description']) : '',
                        'icon' => !empty($item['icon']) ? trim((string)$item['icon']) : 'fa-star',
                        'badge' => isset($item['badge']) ? trim((string)$item['badge']) : '',
                        'status' => !empty($item['status']) ? 1 : 0
                    );
                }
                $data['amenities_json'] = json_encode($cleanAmenities, JSON_UNESCAPED_UNICODE);
                unset($data['amenities_items']);
            }

            // Handle Photo Uploads for all customizable image fields
            $uploadDir = WWW_ROOT . 'img' . DS . 'theme_customizer' . DS;
            try {
                $dir = new Folder($uploadDir, true, 0755);
            } catch (Exception $e) {}

            $imageFields = array(
                'brand_logo_image',
                'intro_f1_image',
                'intro_f2_image',
                'reasons_image',
                'area_card1_image',
                'area_card2_image',
                'area_card3_image',
                'other_image'
            );

            foreach ($imageFields as $imgKey) {
                $fileKey = $imgKey . '_file';
                if (!empty($_FILES[$fileKey]['name']) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                    $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES[$fileKey]['name']);
                    $uniqueName = $imgKey . '_' . time() . '_' . $fileName;
                    $targetPath = $uploadDir . $uniqueName;
                    if (@move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
                        $data[$imgKey] = '/img/theme_customizer/' . $uniqueName;
                    }
                }
            }

            $this->RealEstateThemeSetting->create();
            if ($this->RealEstateThemeSetting->save(array('RealEstateThemeSetting' => $data), false)) {
                try {
                    Cache::clear(false);
                } catch (Exception $e) {}
                $this->Session->setFlash(__('Theme settings, photos and amenities updated successfully!'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'action' => 'settings', 'admin' => true));
            } else {
                $this->Session->setFlash(__('Failed to update theme settings. Please check the form.'), 'flash', array('alert' => 'danger'));
            }
        } else {
            $this->request->data = array('RealEstateThemeSetting' => $themeSetting);
        }

        $amenitiesList = $this->RealEstateThemeSetting->decodeAmenities(!empty($themeSetting['amenities_json']) ? $themeSetting['amenities_json'] : '');
        $module = $this->SystemModule->find('first', array('conditions' => array('SystemModule.slug' => 'RealEstateTheme'), 'recursive' => -1));
        $isActive = !empty($module['SystemModule']['status']);

        $this->set(compact('themeSetting', 'isActive', 'module', 'amenitiesList'));
    }
}
