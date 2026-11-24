<?php
App::uses('AppModel', 'Model');

class FacebookSetting extends AppModel
{
    public $useTable = 'facebook_settings';
    public $primaryKey = 'id';

    public function getSettings()
    {
        $settings = $this->find('first', array(
            'conditions' => array('FacebookSetting.id' => 1),
            'recursive' => -1
        ));
        if(!empty($settings['FacebookSetting']))
        {
            return $settings['FacebookSetting'];
        }
        $defaults = array(
            'id' => 1,
            'app_id' => '',
            'app_secret' => '',
            'page_id' => '',
            'page_name' => '',
            'page_access_token' => '',
            'webhook_verify_token' => 'whitevillas_fb_verify_token_2026',
            'auto_fetch_enabled' => 1,
            'auto_assign_enabled' => 1,
            'default_project_id' => null,
            'default_status_id' => null,
            'last_sync_at' => null
        );
        $this->create();
        $this->save(array('FacebookSetting' => $defaults));
        return $defaults;
    }
}