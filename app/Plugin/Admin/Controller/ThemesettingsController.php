<?php
class ThemesettingsController extends AdminAppController
{
    public $helpers = array('Html','Form','Session');
    public $components = array('Session');
    public $uses = array('ThemeSetting');

    public function index()
    {
        $this->ThemeSetting->ensureSchema();
        $fontOptions = $this->ThemeSetting->fontOptions();
        $adminThemes = $this->ThemeSetting->adminThemes();
        $adminThemeOptions = $this->ThemeSetting->adminThemeOptions();
        $defaults = $this->ThemeSetting->defaults();
        $scopeLabels = array(
            'admin' => __('Backend Admin Panel'),
            'front' => __('Frontend Website'),
            'client' => __('User Dashboard / Client Portal')
        );
        $colorFields = array(
            'primary' => __('Primary Dark'),
            'secondary' => __('Secondary Dark'),
            'accent' => __('Accent / Button'),
            'accent_dark' => __('Accent Dark'),
            'success' => __('Success'),
            'warning' => __('Warning'),
            'info' => __('Info / Cyan'),
            'background' => __('Page Background'),
            'surface' => __('Card / Surface'),
            'surface_soft' => __('Soft Surface'),
            'border' => __('Border'),
            'border_strong' => __('Strong Border'),
            'text' => __('Text'),
            'muted' => __('Muted Text')
        );
        $sizeFields = array(
            'base_size' => __('Base Font Size'),
            'heading_size' => __('Heading Size'),
            'button_size' => __('Button Size'),
            'table_size' => __('Table Text Size'),
            'radius' => __('Card Radius')
        );

        if($this->request->is('post'))
        {
            if(!empty($this->request->data['reset_theme']))
            {
                $this->ThemeSetting->saveSettings($defaults);
                $this->Session->setFlash(__('Theme settings reset to default.'), 'flash', array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            $raw = isset($this->request->data['ThemeSetting']) ? (array)$this->request->data['ThemeSetting'] : array();
            if($this->ThemeSetting->saveSettings($raw))
            {
                $this->Session->setFlash(__('Theme settings have been saved and applied.'), 'flash', array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            $this->Session->setFlash(__('Unable to save theme settings.'), 'flash', array('alert'=>'danger'));
        }

        if(!$this->request->data)
        {
            $this->request->data['ThemeSetting'] = $this->ThemeSetting->currentSettings();
        }
        $this->set(compact('fontOptions','scopeLabels','colorFields','sizeFields','adminThemes','adminThemeOptions'));
    }
}
?>
