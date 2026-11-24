<?php
class ThemeSetting extends AppModel
{
    public $useTable = 'theme_settings';

    protected $_fontOptions = array(
        'Raleway' => '"Raleway","Segoe UI",Arial,sans-serif',
        'Inter' => '"Inter","Segoe UI",Arial,sans-serif',
        'Poppins' => '"Poppins","Segoe UI",Arial,sans-serif',
        'Roboto' => '"Roboto","Segoe UI",Arial,sans-serif',
        'Nunito' => '"Nunito","Segoe UI",Arial,sans-serif',
        'System UI' => '"Segoe UI",Arial,sans-serif',
        'Serif Corporate' => 'Georgia,"Times New Roman",serif'
    );

    protected $_scopes = array('admin', 'front', 'client');

    protected $_adminThemes = array(
        'theme-light' => array(
            'label' => 'Professional Light',
            'description' => 'Corporate light ERP interface with blue accents.',
            'colors' => array(
                '--admin-theme-primary' => '#2563eb',
                '--admin-theme-primary-soft' => '#dbeafe',
                '--admin-theme-accent' => '#0f172a',
                '--admin-theme-accent-2' => '#1d4ed8',
                '--admin-theme-bg' => '#f3f7fc',
                '--admin-theme-surface' => '#ffffff',
                '--admin-theme-surface-2' => '#f8fbff',
                '--admin-theme-border' => '#dbe6f3',
                '--admin-theme-text' => '#10213a',
                '--admin-theme-text-soft' => '#5b6b83',
                '--admin-theme-sidebar' => '#eef4fb',
                '--admin-theme-sidebar-text' => '#18304f',
                '--admin-theme-sidebar-muted' => '#60748f',
                '--admin-theme-sidebar-active' => '#ffffff',
                '--admin-theme-sidebar-active-text' => '#1d4ed8',
                '--admin-theme-topbar' => '#ffffff',
                '--admin-theme-topbar-text' => '#10213a',
                '--admin-theme-shadow' => '0 18px 40px rgba(15, 23, 42, 0.08)'
            )
        ),
        'theme-dark' => array(
            'label' => 'Professional Dark',
            'description' => 'Dark charcoal workspace with premium contrast.',
            'colors' => array(
                '--admin-theme-primary' => '#60a5fa',
                '--admin-theme-primary-soft' => '#1e3a5f',
                '--admin-theme-accent' => '#0f172a',
                '--admin-theme-accent-2' => '#1d4ed8',
                '--admin-theme-bg' => '#0b1220',
                '--admin-theme-surface' => '#121c2d',
                '--admin-theme-surface-2' => '#172235',
                '--admin-theme-border' => '#26354b',
                '--admin-theme-text' => '#f8fbff',
                '--admin-theme-text-soft' => '#9eb0c9',
                '--admin-theme-sidebar' => '#09111d',
                '--admin-theme-sidebar-text' => '#e2ebf8',
                '--admin-theme-sidebar-muted' => '#91a4bf',
                '--admin-theme-sidebar-active' => '#172235',
                '--admin-theme-sidebar-active-text' => '#ffffff',
                '--admin-theme-topbar' => '#121c2d',
                '--admin-theme-topbar-text' => '#f8fbff',
                '--admin-theme-shadow' => '0 20px 48px rgba(0, 0, 0, 0.34)'
            )
        ),
        'theme-gold' => array(
            'label' => 'Royal Gold',
            'description' => 'Luxury white and gold presentation for executive use.',
            'colors' => array(
                '--admin-theme-primary' => '#b8860b',
                '--admin-theme-primary-soft' => '#f6e7b2',
                '--admin-theme-accent' => '#4b3a09',
                '--admin-theme-accent-2' => '#c99816',
                '--admin-theme-bg' => '#faf7ef',
                '--admin-theme-surface' => '#ffffff',
                '--admin-theme-surface-2' => '#fffcf3',
                '--admin-theme-border' => '#ead9a6',
                '--admin-theme-text' => '#2f2a1f',
                '--admin-theme-text-soft' => '#73684b',
                '--admin-theme-sidebar' => '#fff8e1',
                '--admin-theme-sidebar-text' => '#4c3e18',
                '--admin-theme-sidebar-muted' => '#8b7a4b',
                '--admin-theme-sidebar-active' => '#ffffff',
                '--admin-theme-sidebar-active-text' => '#8a6500',
                '--admin-theme-topbar' => '#ffffff',
                '--admin-theme-topbar-text' => '#2f2a1f',
                '--admin-theme-shadow' => '0 18px 40px rgba(122, 91, 6, 0.12)'
            )
        ),
        'theme-green' => array(
            'label' => 'Emerald Green',
            'description' => 'Fresh green navigation with a white professional workspace.',
            'colors' => array(
                '--admin-theme-primary' => '#059669',
                '--admin-theme-primary-soft' => '#d1fae5',
                '--admin-theme-accent' => '#064e3b',
                '--admin-theme-accent-2' => '#047857',
                '--admin-theme-bg' => '#f0fdf4',
                '--admin-theme-surface' => '#ffffff',
                '--admin-theme-surface-2' => '#f7fffb',
                '--admin-theme-border' => '#bbf7d0',
                '--admin-theme-text' => '#083d2d',
                '--admin-theme-text-soft' => '#4c796b',
                '--admin-theme-sidebar' => '#ecfdf5',
                '--admin-theme-sidebar-text' => '#065f46',
                '--admin-theme-sidebar-muted' => '#4d7c6f',
                '--admin-theme-sidebar-active' => '#ffffff',
                '--admin-theme-sidebar-active-text' => '#047857',
                '--admin-theme-topbar' => '#ffffff',
                '--admin-theme-topbar-text' => '#083d2d',
                '--admin-theme-shadow' => '0 18px 40px rgba(6, 95, 70, 0.10)'
            )
        )
    );

    public function ensureSchema()
    {
        static $checked = false;
        if($checked)
        {
            return true;
        }
        $checked = true;
        try
        {
            $this->query(
                "CREATE TABLE IF NOT EXISTS `theme_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `settings_json` longtext NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            $this->schema(true);
        }
        catch(Exception $e)
        {
            try
            {
                $this->query(
                    "CREATE TABLE IF NOT EXISTS `theme_settings` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `settings_json` longtext NULL,
                        `created` datetime DEFAULT NULL,
                        `modified` datetime DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
                );
                $this->schema(true);
            }
            catch(Exception $ignored)
            {
                return false;
            }
        }
        return true;
    }

    public function fontOptions()
    {
        return array_combine(array_keys($this->_fontOptions), array_keys($this->_fontOptions));
    }

    public function adminThemes()
    {
        return $this->_adminThemes;
    }

    public function adminThemeOptions()
    {
        $options = array();
        foreach($this->_adminThemes as $key => $theme)
        {
            $options[$key] = $theme['label'];
        }
        return $options;
    }

    public function normalizeThemeKey($value, $default = 'theme-light')
    {
        $value = trim((string)$value);
        if(isset($this->_adminThemes[$value]))
        {
            return $value;
        }
        return isset($this->_adminThemes[$default]) ? $default : 'theme-light';
    }

    public function defaults()
    {
        $defaults = array(
            'admin_default_theme' => 'theme-light'
        );
        $scopeDefaults = array(
            'admin' => array(
                'primary' => '#07152a',
                'secondary' => '#102746',
                'accent' => '#ff0a46',
                'accent_dark' => '#c70032',
                'success' => '#047857',
                'warning' => '#ffb000',
                'info' => '#7cf9ff',
                'background' => '#f4f7fb',
                'surface' => '#ffffff',
                'surface_soft' => '#f8fbff',
                'border' => '#dfe8f3',
                'border_strong' => '#cfdbe9',
                'text' => '#07152a',
                'muted' => '#64748b',
                'font' => 'Raleway',
                'base_size' => '13',
                'heading_size' => '22',
                'button_size' => '12',
                'table_size' => '12.5',
                'radius' => '14'
            ),
            'front' => array(
                'primary' => '#07152a',
                'secondary' => '#102746',
                'accent' => '#ff0a46',
                'accent_dark' => '#c70032',
                'success' => '#047857',
                'warning' => '#ffb000',
                'info' => '#7cf9ff',
                'background' => '#f4f7fb',
                'surface' => '#ffffff',
                'surface_soft' => '#f8fbff',
                'border' => '#dfe8f3',
                'border_strong' => '#cfdbe9',
                'text' => '#07152a',
                'muted' => '#64748b',
                'font' => 'Raleway',
                'base_size' => '14',
                'heading_size' => '28',
                'button_size' => '13',
                'table_size' => '13',
                'radius' => '14'
            ),
            'client' => array(
                'primary' => '#07152a',
                'secondary' => '#102746',
                'accent' => '#ff0a46',
                'accent_dark' => '#c70032',
                'success' => '#047857',
                'warning' => '#ffb000',
                'info' => '#7cf9ff',
                'background' => '#f4f7fb',
                'surface' => '#ffffff',
                'surface_soft' => '#f8fbff',
                'border' => '#dfe8f3',
                'border_strong' => '#cfdbe9',
                'text' => '#07152a',
                'muted' => '#64748b',
                'font' => 'Raleway',
                'base_size' => '13',
                'heading_size' => '24',
                'button_size' => '12',
                'table_size' => '12.5',
                'radius' => '14'
            )
        );
        foreach($scopeDefaults as $scope => $rows)
        {
            foreach($rows as $key => $value)
            {
                $defaults[$scope.'_'.$key] = $value;
            }
        }
        return $defaults;
    }

    public function currentSettings()
    {
        $this->ensureSchema();
        $defaults = $this->defaults();
        $row = (array)$this->find('first', array(
            'conditions' => array('ThemeSetting.id' => 1),
            'recursive' => -1
        ));
        if(empty($row['ThemeSetting']['id']))
        {
            $this->saveSettings($defaults);
            return $defaults;
        }
        $stored = array();
        if(!empty($row['ThemeSetting']['settings_json']))
        {
            $decoded = json_decode((string)$row['ThemeSetting']['settings_json'], true);
            if(is_array($decoded))
            {
                $stored = $decoded;
            }
        }
        return $this->normalizeSettings(array_merge($defaults, $stored));
    }

    public function saveSettings($rawSettings = array())
    {
        $this->ensureSchema();
        $settings = $this->normalizeSettings($rawSettings);
        $this->create();
        return $this->save(array('ThemeSetting' => array(
            'id' => 1,
            'settings_json' => json_encode($settings)
        )), array('validate' => false));
    }

    public function normalizeSettings($rawSettings = array())
    {
        $rawSettings = is_array($rawSettings) ? $rawSettings : array();
        $defaults = $this->defaults();
        $settings = array();
        foreach($defaults as $key => $defaultValue)
        {
            $value = array_key_exists($key, $rawSettings) ? $rawSettings[$key] : $defaultValue;
            if($this->_isColorKey($key))
            {
                $settings[$key] = $this->_normalizeColor($value, $defaultValue);
            }
            elseif(substr($key, -5) === '_font')
            {
                $settings[$key] = isset($this->_fontOptions[$value]) ? $value : $defaultValue;
            }
            elseif($key === 'admin_default_theme')
            {
                $settings[$key] = $this->normalizeThemeKey($value, $defaultValue);
            }
            elseif(substr($key, -5) === '_size' || substr($key, -7) === '_radius')
            {
                $settings[$key] = $this->_normalizeNumber($value, $defaultValue, (substr($key, -7) === '_radius') ? 4 : 9, (substr($key, -7) === '_radius') ? 28 : 42);
            }
            else
            {
                $settings[$key] = trim((string)$value);
            }
        }
        return $settings;
    }

    protected function _isColorKey($key)
    {
        foreach(array('_primary','_secondary','_accent','_accent_dark','_success','_warning','_info','_background','_surface','_surface_soft','_border','_border_strong','_text','_muted') as $suffix)
        {
            if(substr($key, -strlen($suffix)) === $suffix)
            {
                return true;
            }
        }
        return false;
    }

    protected function _normalizeColor($value, $default)
    {
        $value = trim((string)$value);
        if(preg_match('/^#[0-9a-fA-F]{6}$/', $value))
        {
            return strtolower($value);
        }
        if(preg_match('/^#[0-9a-fA-F]{3}$/', $value))
        {
            return strtolower($value);
        }
        return $default;
    }

    protected function _normalizeNumber($value, $default, $min, $max)
    {
        $value = preg_replace('/[^0-9.]/', '', (string)$value);
        if($value === '')
        {
            $value = $default;
        }
        $number = (float)$value;
        if($number < $min)
        {
            $number = $min;
        }
        if($number > $max)
        {
            $number = $max;
        }
        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }

    protected function _fontStack($fontKey)
    {
        return isset($this->_fontOptions[$fontKey]) ? $this->_fontOptions[$fontKey] : $this->_fontOptions['Raleway'];
    }

    public function dynamicCss($settings = array())
    {
        $settings = $this->normalizeSettings(empty($settings) ? $this->currentSettings() : $settings);
        $css = array();
        $css[] = "@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Nunito:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700;800;900&family=Raleway:wght@400;500;600;700;800;900&family=Roboto:wght@400;500;700;900&display=swap');";
        $map = array(
            'primary' => '--wv-navy',
            'secondary' => '--wv-navy-2',
            'accent' => '--wv-red',
            'accent_dark' => '--wv-red-dark',
            'success' => '--wv-green',
            'warning' => '--wv-gold',
            'info' => '--wv-cyan',
            'background' => '--wv-bg',
            'surface' => '--wv-surface',
            'surface_soft' => '--wv-surface-soft',
            'border' => '--wv-border',
            'border_strong' => '--wv-border-strong',
            'text' => '--wv-text',
            'muted' => '--wv-muted'
        );
        $bodySelector = array(
            'admin' => 'body.wv-admin-theme',
            'front' => 'body.wv-front-theme',
            'client' => 'body.wv-client-theme'
        );
        foreach($this->_scopes as $scope)
        {
            $selector = $bodySelector[$scope];
            $lines = array();
            foreach($map as $key => $cssVar)
            {
                $lines[] = $cssVar.':'.$settings[$scope.'_'.$key];
            }
            $lines[] = '--wv-font:'.$this->_fontStack($settings[$scope.'_font']);
            $lines[] = '--wv-radius-lg:'.$settings[$scope.'_radius'].'px';
            $css[] = $selector.'{'.implode(';', $lines).';font-family:var(--wv-font)!important;font-size:'.$settings[$scope.'_base_size'].'px!important;}';
        }
        foreach($this->_adminThemes as $themeKey => $theme)
        {
            $themeLines = array();
            foreach((array)$theme['colors'] as $varName => $varValue)
            {
                $themeLines[] = $varName.':'.$varValue;
            }
            $css[] = 'body.wv-admin-theme.'.$themeKey.'{'.implode(';', $themeLines).';}';
        }
        $css[] = "body.wv-admin-theme .page-title .title,body.wv-admin-theme .page-title h1{font-size:".$settings['admin_heading_size']."px!important;}";
        $css[] = "body.wv-admin-theme h1,body.wv-admin-theme h2,body.wv-admin-theme h3,body.wv-admin-theme h4{font-family:var(--wv-font)!important;}";
        $css[] = "body.wv-admin-theme .btn{font-size:".$settings['admin_button_size']."px!important;}";
        $css[] = "body.wv-admin-theme .table>tbody>tr>td,body.wv-admin-theme table.table>tbody>tr>td{font-size:".$settings['admin_table_size']."px!important;}";
        $css[] = "body.wv-admin-theme .table>thead>tr>th,body.wv-admin-theme table.table>thead>tr>th{font-size:".max(10, ((float)$settings['admin_table_size'] - 0.5))."px!important;}";

        $css[] = "body.wv-front-theme .content-title h1,body.wv-front-theme .page-header h1,body.wv-front-theme h1{font-size:".$settings['front_heading_size']."px!important;}";
        $css[] = "body.wv-front-theme h1,body.wv-front-theme h2,body.wv-front-theme h3,body.wv-front-theme h4{font-family:var(--wv-font)!important;}";
        $css[] = "body.wv-front-theme .btn,body.wv-front-theme button{font-size:".$settings['front_button_size']."px!important;}";
        $css[] = "body.wv-front-theme .table>tbody>tr>td,body.wv-front-theme table.table>tbody>tr>td{font-size:".$settings['front_table_size']."px!important;}";

        $css[] = "body.wv-client-theme h1,body.wv-client-theme h2,body.wv-client-theme .dashboard-title{font-size:".$settings['client_heading_size']."px!important;}";
        $css[] = "body.wv-client-theme h1,body.wv-client-theme h2,body.wv-client-theme h3,body.wv-client-theme h4{font-family:var(--wv-font)!important;}";
        $css[] = "body.wv-client-theme .btn,body.wv-client-theme button{font-size:".$settings['client_button_size']."px!important;}";
        $css[] = "body.wv-client-theme .table>tbody>tr>td,body.wv-client-theme table.table>tbody>tr>td{font-size:".$settings['client_table_size']."px!important;}";
        return implode("\n", $css);
    }
}
?>
