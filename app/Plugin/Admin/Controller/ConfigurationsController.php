<?php
App::uses('Router', 'Routing');
class ConfigurationsController extends AdminAppController {
    public $helpers = array('Html','Form','Session');
    public $components = array('Session','search-master.Prg');
    protected function _moneyValue($value)
    {
        $value = str_replace(',', '', (string)$value);
        $value = preg_replace('/[^0-9\.\-]/', '', $value);
        if($value === '' || !is_numeric($value))
        {
            return 0;
        }
        return max(0, (float)$value);
    }
    protected function _paymentPlanAgencyOptions()
    {
        $this->loadModel('Agency');
        $conditions = array();
        if(!$this->_isSuperAdminSession((array)$this->adminValue) && (int)$this->agencyId > 0)
        {
            $conditions['Agency.id'] = (int)$this->agencyId;
        }
        return (array)$this->Agency->find('list', array(
            'fields' => array('Agency.id','Agency.name'),
            'conditions' => $conditions,
            'order' => array('Agency.name' => 'asc'),
            'recursive' => -1
        ));
    }
    protected function _selectedPaymentPlanProjectId($agencyOptions)
    {
        if($this->_isSuperAdminSession((array)$this->adminValue) && !empty($this->request->query['project_id']))
        {
            $projectId = (int)$this->request->query['project_id'];
            if(isset($agencyOptions[$projectId]))
            {
                return $projectId;
            }
        }
        if(!$this->_isSuperAdminSession((array)$this->adminValue) && (int)$this->agencyId > 0)
        {
            return (int)$this->agencyId;
        }
        foreach((array)$agencyOptions as $id => $name)
        {
            return (int)$id;
        }
        return 0;
    }
    protected function _paymentPlanPageForProject($projectId)
    {
        $projectId = (int)$projectId;
        $this->loadModel('AgencyPaymentPlanPage');
        $this->AgencyPaymentPlanPage->ensureSchema();
        $page = (array)$this->AgencyPaymentPlanPage->find('first', array(
            'fields' => array('AgencyPaymentPlanPage.*'),
            'conditions' => array('AgencyPaymentPlanPage.project_id' => $projectId),
            'recursive' => -1
        ));
        if(!empty($page['AgencyPaymentPlanPage']['id']))
        {
            $page['AgencyPaymentPlanPage'] = $this->_applyPaymentPlanTitleDefaults((array)$page['AgencyPaymentPlanPage']);
            return $page;
        }
        $this->loadModel('Agency');
        $agency = (array)$this->Agency->find('first', array(
            'conditions' => array('Agency.id' => $projectId),
            'recursive' => -1
        ));
        if(empty($agency['Agency']))
        {
            $agency = array('Agency' => array('id' => $projectId, 'name' => __('Agency')));
        }
        return array('AgencyPaymentPlanPage' => $this->AgencyPaymentPlanPage->defaultsForAgency($agency));
    }
    protected function _paymentPlanTitleDefaults()
    {
        return array(
            'summary_title' => __('Main Amounts'),
            'footer_title' => __('Need updated availability?'),
            'footer_text' => __('Contact this agency for current inventory, booking confirmation, and latest price updates.')
        );
    }
    protected function _applyPaymentPlanTitleDefaults($data)
    {
        $data = (array)$data;
        foreach($this->_paymentPlanTitleDefaults() as $field => $default)
        {
            if(!isset($data[$field]) || trim((string)$data[$field]) === '')
            {
                $data[$field] = $default;
            }
        }
        return $data;
    }
    protected function _ensureDefaultMapProviderColumn()
    {
        if($this->Configuration->hasField('default_map_provider'))
        {
            return true;
        }
        try
        {
            $this->Configuration->query("ALTER TABLE `configurations` ADD `default_map_provider` VARCHAR(10) NOT NULL DEFAULT 'osm' AFTER `map_key`");
            $this->Configuration->schema(true);
        }
        catch (Exception $e)
        {
            $this->Configuration->schema(true);
        }
        return $this->Configuration->hasField('default_map_provider');
    }
    protected function _ensureReceiptSummaryCardColumn()
    {
        if($this->Configuration->hasField('show_receipt_summary_card'))
        {
            return true;
        }
        try
        {
            $this->Configuration->query("ALTER TABLE `configurations` ADD `show_receipt_summary_card` TINYINT(1) NOT NULL DEFAULT 1 AFTER `late_fees`");
            $this->Configuration->schema(true);
        }
        catch (Exception $e)
        {
            $this->Configuration->schema(true);
        }
        return $this->Configuration->hasField('show_receipt_summary_card');
    }
    protected function _ensureReceiptPrintDateColumn()
    {
        if($this->Configuration->hasField('show_receipt_print_date'))
        {
            return true;
        }
        try
        {
            $this->Configuration->query("ALTER TABLE `configurations` ADD `show_receipt_print_date` TINYINT(1) NOT NULL DEFAULT 1");
            $this->Configuration->schema(true);
        }
        catch (Exception $e)
        {
            $this->Configuration->schema(true);
        }
        return $this->Configuration->hasField('show_receipt_print_date');
    }
    protected function _ensureFeaturedTickerColumns()
    {
        $columns = array(
            'featured_ticker_enabled' => "ALTER TABLE `configurations` ADD `featured_ticker_enabled` TINYINT(1) NOT NULL DEFAULT 1 AFTER `slides`",
            'featured_ticker_speed' => "ALTER TABLE `configurations` ADD `featured_ticker_speed` INT(11) NOT NULL DEFAULT 35 AFTER `featured_ticker_enabled`",
            'featured_ticker_effect' => "ALTER TABLE `configurations` ADD `featured_ticker_effect` VARCHAR(20) NOT NULL DEFAULT 'linear' AFTER `featured_ticker_speed`"
        );
        $allExist = true;
        foreach($columns as $columnName => $sql)
        {
            if($this->Configuration->hasField($columnName))
            {
                continue;
            }
            $allExist = false;
            try
            {
                $this->Configuration->query($sql);
                $this->Configuration->schema(true);
            }
            catch (Exception $e)
            {
                $this->Configuration->schema(true);
            }
        }
        if(!$allExist)
        {
            $this->Configuration->schema(true);
        }
        return (
            $this->Configuration->hasField('featured_ticker_enabled') &&
            $this->Configuration->hasField('featured_ticker_speed') &&
            $this->Configuration->hasField('featured_ticker_effect')
        );
    }
        protected function _ensureFaviconColumn()
    {
        if($this->Configuration->hasField('favicon'))
        {
            return true;
        }
        try
        {
            $this->Configuration->query("ALTER TABLE `configurations` ADD `favicon` VARCHAR(255) NULL DEFAULT NULL AFTER `photo`");
            $this->Configuration->schema(true);
        }
        catch (Exception $e)
        {
            $this->Configuration->schema(true);
        }
        return $this->Configuration->hasField('favicon');
    }
    protected function _ensureDealFeatureColumns()
    {
        $columns = array(
            'deal_vault_enabled' => "ALTER TABLE `configurations` ADD `deal_vault_enabled` TINYINT(1) NOT NULL DEFAULT 1",
            'deal_risk_score_enabled' => "ALTER TABLE `configurations` ADD `deal_risk_score_enabled` TINYINT(1) NOT NULL DEFAULT 1",
            'deal_cashflow_forecast_enabled' => "ALTER TABLE `configurations` ADD `deal_cashflow_forecast_enabled` TINYINT(1) NOT NULL DEFAULT 1",
            'deal_features_admin_only' => "ALTER TABLE `configurations` ADD `deal_features_admin_only` TINYINT(1) NOT NULL DEFAULT 1"
        );
        $allExist = true;
        foreach($columns as $columnName => $sql)
        {
            if($this->Configuration->hasField($columnName))
            {
                continue;
            }
            $allExist = false;
            try
            {
                $this->Configuration->query($sql);
                $this->Configuration->schema(true);
            }
            catch (Exception $e)
            {
                $this->Configuration->schema(true);
            }
        }
        if(!$allExist)
        {
            $this->Configuration->schema(true);
        }
        return (
            $this->Configuration->hasField('deal_vault_enabled') &&
            $this->Configuration->hasField('deal_risk_score_enabled') &&
            $this->Configuration->hasField('deal_cashflow_forecast_enabled') &&
            $this->Configuration->hasField('deal_features_admin_only')
        );
    }
    public function index()
    {
        $hasReceiptSummaryCardColumn = $this->_ensureReceiptSummaryCardColumn();
        $hasReceiptPrintDateColumn = $this->_ensureReceiptPrintDateColumn();
        $hasDefaultMapProviderColumn = $this->_ensureDefaultMapProviderColumn();
        $hasFeaturedTickerColumns = $this->_ensureFeaturedTickerColumns();
        $this->_ensureFaviconColumn();
        $hasDealFeatureColumns = $this->_ensureDealFeatureColumns();
        $this->set('hasDealFeatureColumns',$hasDealFeatureColumns);
        $this->set('hasReceiptSummaryCardColumn',$hasReceiptSummaryCardColumn);
        $this->set('hasReceiptPrintDateColumn',$hasReceiptPrintDateColumn);
        $this->set('hasDefaultMapProviderColumn',$hasDefaultMapProviderColumn);
        $this->set('hasFeaturedTickerColumns',$hasFeaturedTickerColumns);
        $this->loadModel('Currency');
        $this->set('currency',$this->Currency->find('list',array('fields'=>array('id','name'))));
        $this->set('mapProviderOptions',array('osm'=>__('OpenStreetMap'),'google'=>__('Google Map')));
        $this->set('featuredTickerEffectOptions',array('linear'=>__('Smooth'),'ease'=>__('Soft'),'ease-in-out'=>__('Balanced')));
        $language=array('af'=>'Afrikaans','sq'=>'Albanian','ar'=>'Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©','hy'=>'Armenian/Armenia','eu'=>'Basque','bs'=>'Bosnian','bg'=>'Bulgarian','be'=>'Byelorussian','ca'=>'Catalan','zh'=>'ä¸­æ–‡','cs'=>'Czech','da'=>'Danish','nl'=>'Dutch','en'=>'English','et'=>'Estonian','fo'=>'Faeroese','fa'=>'ÙØ§Ø±Ø³ÛŒ','fi'=>'Finnish','fr'=>'French','gl'=>'Galician','de'=>'German','el'=>'Greek','he'=>'×¢Ö´×‘×¨Ö´×™×ª','hi'=>'Hindi','hu'=>'Hungarian','is'=>'Icelandic','id'=>'Indonesian','ga'=>'Irish','it'=>'Italian','ja'=>'æ—¥æœ¬èªž','kk'=>'Kazakh','ko'=>'Korean','lv'=>'Latvian','lt'=>'Lithuanian','mk'=>'Macedonian','ms'=>'Malaysian','mt'=>'Maltese','no'=>'Norwegian','pl'=>'Polish','pt'=>'Portuguese','ro'=>'Romanian','ru'=>'Russian','se'=>'Sami','sr'=>'Serbian','sk'=>'Slovak','sl'=>'Slovenian','sb'=>'Sorbian','es'=>'Spanish','sv'=>'Swedish','th'=>'Thai','ts'=>'Tsonga','tn'=>'Tswana','tr'=>'Turkish','uk'=>'Ukrainian','ur'=>'Urdu','ve'=>'Venda','vi'=>'Vietnamese','cy'=>'Welsh','xh'=>'Xhosa','yi'=>'Yiddish','zu'=>'Zulu');
        $id=1;
        $post = $this->Configuration->findById($id);
        $dbReceiptSummaryCard = null;
        $dbReceiptPrintDate = null;
        try
        {
            $selectFields = array('id');
            if($hasReceiptSummaryCardColumn){ $selectFields[] = 'show_receipt_summary_card'; }
            if($hasReceiptPrintDateColumn){ $selectFields[] = 'show_receipt_print_date'; }
            $rawToggleRows = $this->Configuration->query("SELECT ".implode(',', $selectFields)." FROM configurations WHERE id=".(int)$id." LIMIT 1");
            $rawToggle = array();
            if(!empty($rawToggleRows[0]['configurations']) && is_array($rawToggleRows[0]['configurations']))
            {
                $rawToggle = $rawToggleRows[0]['configurations'];
            }
            elseif(!empty($rawToggleRows[0][0]) && is_array($rawToggleRows[0][0]))
            {
                $rawToggle = $rawToggleRows[0][0];
            }
            if($hasReceiptSummaryCardColumn && isset($rawToggle['show_receipt_summary_card']))
            {
                $dbReceiptSummaryCard = (int)$rawToggle['show_receipt_summary_card'];
                if(!isset($post['Configuration']) || !is_array($post['Configuration'])){$post['Configuration']=array();}
                $post['Configuration']['show_receipt_summary_card'] = $dbReceiptSummaryCard;
            }
            if($hasReceiptPrintDateColumn && isset($rawToggle['show_receipt_print_date']))
            {
                $dbReceiptPrintDate = (int)$rawToggle['show_receipt_print_date'];
                if(!isset($post['Configuration']) || !is_array($post['Configuration'])){$post['Configuration']=array();}
                $post['Configuration']['show_receipt_print_date'] = $dbReceiptPrintDate;
            }
        }
        catch(Exception $e)
        {
            $dbReceiptSummaryCard = null;
            $dbReceiptPrintDate = null;
        }
        if ($this->request->is('post'))
        {
            $this->Configuration->id = $id;
            if(!$hasReceiptSummaryCardColumn)
            {
                unset($this->request->data['Configuration']['show_receipt_summary_card']);
            }
            if(!$hasReceiptPrintDateColumn)
            {
                unset($this->request->data['Configuration']['show_receipt_print_date']);
            }
            if(!$hasDefaultMapProviderColumn)
            {
                unset($this->request->data['Configuration']['default_map_provider']);
            }
            if(!isset($this->request->data['Configuration']) || !is_array($this->request->data['Configuration']))
            {
                $this->request->data['Configuration'] = array();
            }
            if($hasReceiptSummaryCardColumn)
            {
                $summaryToggleValue = isset($this->request->data['Configuration']['show_receipt_summary_card']) ? $this->request->data['Configuration']['show_receipt_summary_card'] : 0;
                if(is_array($summaryToggleValue))
                {
                    $summaryToggleValue = end($summaryToggleValue);
                }
                $this->request->data['Configuration']['show_receipt_summary_card'] = ((string)$summaryToggleValue === '1' || (int)$summaryToggleValue === 1) ? 1 : 0;
            }
            if($hasReceiptPrintDateColumn)
            {
                $printDateToggleValue = isset($this->request->data['Configuration']['show_receipt_print_date']) ? $this->request->data['Configuration']['show_receipt_print_date'] : 0;
                if(is_array($printDateToggleValue))
                {
                    $printDateToggleValue = end($printDateToggleValue);
                }
                $this->request->data['Configuration']['show_receipt_print_date'] = ((string)$printDateToggleValue === '1' || (int)$printDateToggleValue === 1) ? 1 : 0;
            }
            if($hasFeaturedTickerColumns)
            {
                $this->request->data['Configuration']['featured_ticker_enabled'] = !empty($this->request->data['Configuration']['featured_ticker_enabled']) ? 1 : 0;
                $tickerSpeedValue = isset($this->request->data['Configuration']['featured_ticker_speed']) ? (int)$this->request->data['Configuration']['featured_ticker_speed'] : 35;
                if($tickerSpeedValue < 10){ $tickerSpeedValue = 10; }
                if($tickerSpeedValue > 180){ $tickerSpeedValue = 180; }
                $this->request->data['Configuration']['featured_ticker_speed'] = $tickerSpeedValue;
                $tickerEffectValue = isset($this->request->data['Configuration']['featured_ticker_effect']) ? strtolower(trim((string)$this->request->data['Configuration']['featured_ticker_effect'])) : 'linear';
                if(!in_array($tickerEffectValue,array('linear','ease','ease-in-out')))
                {
                    $tickerEffectValue = 'linear';
                }
                $this->request->data['Configuration']['featured_ticker_effect'] = $tickerEffectValue;
            }
            else
            {
                unset($this->request->data['Configuration']['featured_ticker_enabled']);
                unset($this->request->data['Configuration']['featured_ticker_speed']);
                unset($this->request->data['Configuration']['featured_ticker_effect']);
            }
            if($hasDealFeatureColumns)
            {
                $this->request->data['Configuration']['deal_vault_enabled'] = !empty($this->request->data['Configuration']['deal_vault_enabled']) ? 1 : 0;
                $this->request->data['Configuration']['deal_risk_score_enabled'] = !empty($this->request->data['Configuration']['deal_risk_score_enabled']) ? 1 : 0;
                $this->request->data['Configuration']['deal_cashflow_forecast_enabled'] = !empty($this->request->data['Configuration']['deal_cashflow_forecast_enabled']) ? 1 : 0;
                $this->request->data['Configuration']['deal_features_admin_only'] = !empty($this->request->data['Configuration']['deal_features_admin_only']) ? 1 : 0;
            }
            else
            {
                unset($this->request->data['Configuration']['deal_vault_enabled']);
                unset($this->request->data['Configuration']['deal_risk_score_enabled']);
                unset($this->request->data['Configuration']['deal_cashflow_forecast_enabled']);
                unset($this->request->data['Configuration']['deal_features_admin_only']);
            }
            try
            {
                            // Handle Site Logo upload / removal
            if (!empty($this->request->data['Configuration']['remove_logo'])) {
                $this->request->data['Configuration']['photo'] = '';
                $this->Configuration->id = $id;
                $this->Configuration->saveField('photo', '', array('validate' => false));
                $this->Configuration->query("UPDATE configurations SET photo='' WHERE id=".(int)$id." LIMIT 1");
            } elseif (isset($_FILES['logo_file']) && is_uploaded_file($_FILES['logo_file']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'), true)) {
                    $logoFilename = 'site-logo-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . $ext;
                    $targetDir = WWW_ROOT . 'img';
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0777, true);
                    }
                    $targetPath = $targetDir . DS . $logoFilename;
                    if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $targetPath)) {
                        $this->request->data['Configuration']['photo'] = $logoFilename;
                        $this->Configuration->id = $id;
                        $this->Configuration->saveField('photo', $logoFilename, array('validate' => false));
                        $this->Configuration->query("UPDATE configurations SET photo=".$this->Configuration->getDataSource()->value($logoFilename)." WHERE id=".(int)$id." LIMIT 1");
                    }
                }
            }

            // Handle Site Favicon upload / removal
            if (!empty($this->request->data['Configuration']['remove_favicon'])) {
                $this->request->data['Configuration']['favicon'] = '';
                $this->Configuration->id = $id;
                $this->Configuration->saveField('favicon', '', array('validate' => false));
                $this->Configuration->query("UPDATE configurations SET favicon='' WHERE id=".(int)$id." LIMIT 1");
            } elseif (isset($_FILES['favicon_file']) && is_uploaded_file($_FILES['favicon_file']['tmp_name'])) {
                $ext = strtolower(pathinfo($_FILES['favicon_file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, array('ico', 'png', 'jpg', 'jpeg', 'svg', 'gif', 'webp'), true)) {
                    $favFilename = 'site-favicon-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . $ext;
                    $targetDir = WWW_ROOT . 'img';
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0777, true);
                    }
                    $targetPath = $targetDir . DS . $favFilename;
                    if (move_uploaded_file($_FILES['favicon_file']['tmp_name'], $targetPath)) {
                        $this->request->data['Configuration']['favicon'] = $favFilename;
                        $this->Configuration->id = $id;
                        $this->Configuration->saveField('favicon', $favFilename, array('validate' => false));
                        $this->Configuration->query("UPDATE configurations SET favicon=".$this->Configuration->getDataSource()->value($favFilename)." WHERE id=".(int)$id." LIMIT 1");
                        if ($ext === 'ico') {
                            @copy($targetPath, WWW_ROOT . 'favicon.ico');
                        }
                    }
                }
            }

            if ($this->Configuration->save($this->request->data, array('validate'=>false)))
                {
                    if($hasReceiptSummaryCardColumn && isset($this->request->data['Configuration']['show_receipt_summary_card']))
                    {
                        $this->Configuration->id = $id;
                        $this->Configuration->saveField('show_receipt_summary_card', (int)$this->request->data['Configuration']['show_receipt_summary_card'], array('validate' => false));
                        $this->Configuration->query("UPDATE configurations SET show_receipt_summary_card=".(int)$this->request->data['Configuration']['show_receipt_summary_card']." WHERE id=".(int)$id." LIMIT 1");
                    }
                    if($hasReceiptPrintDateColumn && isset($this->request->data['Configuration']['show_receipt_print_date']))
                    {
                        $this->Configuration->id = $id;
                        $this->Configuration->saveField('show_receipt_print_date', (int)$this->request->data['Configuration']['show_receipt_print_date'], array('validate' => false));
                        $this->Configuration->query("UPDATE configurations SET show_receipt_print_date=".(int)$this->request->data['Configuration']['show_receipt_print_date']." WHERE id=".(int)$id." LIMIT 1");
                    }
                    $this->Session->setFlash(__('Your Setting has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
                $this->Session->setFlash(__('Unable to save settings values. Please try again.'),'flash',array('alert'=>'danger'));
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Setting Problem.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
        if(!isset($this->request->data['Configuration']['default_map_provider']) || strlen((string)$this->request->data['Configuration']['default_map_provider'])<=0)
        {
            $this->request->data['Configuration']['default_map_provider']='osm';
        }
        if(!isset($this->request->data['Configuration']['featured_ticker_enabled']))
        {
            $this->request->data['Configuration']['featured_ticker_enabled']=1;
        }
        if(!isset($this->request->data['Configuration']['featured_ticker_speed']) || (int)$this->request->data['Configuration']['featured_ticker_speed']<=0)
        {
            $this->request->data['Configuration']['featured_ticker_speed']=35;
        }
        if(!isset($this->request->data['Configuration']['featured_ticker_effect']) || strlen(trim((string)$this->request->data['Configuration']['featured_ticker_effect']))<=0)
        {
            $this->request->data['Configuration']['featured_ticker_effect']='linear';
        }
        if($hasReceiptSummaryCardColumn && !isset($this->request->data['Configuration']['show_receipt_summary_card']))
        {
            $this->request->data['Configuration']['show_receipt_summary_card'] = ($dbReceiptSummaryCard === null) ? 1 : (int)$dbReceiptSummaryCard;
        }
        if($hasReceiptPrintDateColumn && !isset($this->request->data['Configuration']['show_receipt_print_date']))
        {
            $this->request->data['Configuration']['show_receipt_print_date'] = ($dbReceiptPrintDate === null) ? 1 : (int)$dbReceiptPrintDate;
        }
        $this->set('language',$language);
    }

    public function dealsettings()
    {
        $hasDealFeatureColumns = $this->_ensureDealFeatureColumns();
        $this->set('hasDealFeatureColumns', $hasDealFeatureColumns);

        $id = 1;
        $this->Configuration->id = $id;

        $isAdmin = ($this->_isSuperAdminSession((array)$this->adminValue) || $this->userType === 'admin' || $this->_isCeoRoleContext((array)$this->adminValue));

        if ($this->request->is('post') || $this->request->is('put')) {
            $currentCfg = $this->Configuration->findById($id);
            $dealAdminOnlyCurrent = !isset($currentCfg['Configuration']['deal_features_admin_only']) || (int)$currentCfg['Configuration']['deal_features_admin_only'] === 1;

            if ($dealAdminOnlyCurrent && !$isAdmin) {
                $this->Session->setFlash(__('Deals & Financial Risk Management Settings are restricted to Administrators.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('controller' => 'Configurations', 'action' => 'dealsettings'));
            }

            if ($hasDealFeatureColumns) {
                $dealVaultEnabled = isset($this->request->data['Configuration']['deal_vault_enabled']) ? (int)$this->request->data['Configuration']['deal_vault_enabled'] : 0;
                $dealRiskScoreEnabled = isset($this->request->data['Configuration']['deal_risk_score_enabled']) ? (int)$this->request->data['Configuration']['deal_risk_score_enabled'] : 0;
                $dealCashflowForecastEnabled = isset($this->request->data['Configuration']['deal_cashflow_forecast_enabled']) ? (int)$this->request->data['Configuration']['deal_cashflow_forecast_enabled'] : 0;
                $dealAdminOnly = isset($this->request->data['Configuration']['deal_features_admin_only']) ? (int)$this->request->data['Configuration']['deal_features_admin_only'] : 0;

                $saveData = array('Configuration' => array(
                    'id' => $id,
                    'deal_vault_enabled' => $dealVaultEnabled,
                    'deal_risk_score_enabled' => $dealRiskScoreEnabled,
                    'deal_cashflow_forecast_enabled' => $dealCashflowForecastEnabled,
                    'deal_features_admin_only' => $dealAdminOnly
                ));

                if ($this->Configuration->save($saveData, array('validate' => false))) {
                    $this->Session->setFlash(__('Deals & Financial Risk Management Settings have been successfully saved.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('controller' => 'Configurations', 'action' => 'dealsettings'));
                } else {
                    $this->Session->setFlash(__('Unable to save settings. Please try again.'), 'flash', array('alert' => 'danger'));
                }
            }
        }

        if (empty($this->request->data)) {
            $this->request->data = $this->Configuration->findById($id);
        }

        $this->set('canManageDealFeatures', $isAdmin);
    }

    public function paymentplans()
    {
        $settingsRoute = array('controller' => $this->params['controller'], 'action' => $this->params['action']);
        $this->loadModel('AgencyPaymentPlanPage');
        if(!$this->AgencyPaymentPlanPage->ensureSchema())
        {
            $this->Session->setFlash(__('Payment plan table could not be prepared. Import app/Config/payment_plan_settings_table.sql in phpMyAdmin, or give this database user CREATE permission once.'),'flash',array('alert'=>'danger'));
            $agencyOptions = array();
            $projectId = 0;
            $isSuperAdminSession = $this->_isSuperAdminSession((array)$this->adminValue);
            $planRows = array();
            $publicUrl = '';
            $paymentPlanTableName = method_exists($this->AgencyPaymentPlanPage, 'paymentPlanTableName') ? $this->AgencyPaymentPlanPage->paymentPlanTableName() : 'agency_payment_plan_pages';
            $paymentPlanTableSql = method_exists($this->AgencyPaymentPlanPage, 'createTableSql') ? $this->AgencyPaymentPlanPage->createTableSql().";" : '';
            $this->set(compact('agencyOptions','projectId','isSuperAdminSession','planRows','publicUrl','paymentPlanTableName','paymentPlanTableSql'));
            return;
        }
        $agencyOptions = $this->_paymentPlanAgencyOptions();
        $projectId = $this->_selectedPaymentPlanProjectId($agencyOptions);
        $isSuperAdminSession = $this->_isSuperAdminSession((array)$this->adminValue);

        if(empty($agencyOptions) || $projectId <= 0)
        {
            $this->Session->setFlash(__('No agency is available for payment plan settings.'),'flash',array('alert'=>'danger'));
            $this->set(compact('agencyOptions','projectId','isSuperAdminSession'));
            return;
        }

        if($this->request->is(array('post','put')))
        {
            $form = !empty($this->request->data['AgencyPaymentPlanPage']) ? (array)$this->request->data['AgencyPaymentPlanPage'] : array();
            $postedProjectId = !empty($form['project_id']) ? (int)$form['project_id'] : $projectId;
            if(!$isSuperAdminSession)
            {
                $postedProjectId = (int)$this->agencyId;
            }
            if(!isset($agencyOptions[$postedProjectId]))
            {
                $this->Session->setFlash(__('You cannot update payment plan settings for this agency.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array_merge($settingsRoute, array('?' => array('project_id' => $projectId))));
            }

            $existing = $this->_paymentPlanPageForProject($postedProjectId);
            $rowId = !empty($existing['AgencyPaymentPlanPage']['id']) ? (int)$existing['AgencyPaymentPlanPage']['id'] : 0;
            if(!empty($form['id']))
            {
                $rowId = (int)$form['id'];
            }
            $slugSource = !empty($form['slug']) ? $form['slug'] : (!empty($form['page_title']) ? $form['page_title'] : $agencyOptions[$postedProjectId]);
            $amountPlans = method_exists($this->AgencyPaymentPlanPage, 'normalizeAmountPlans') ? $this->AgencyPaymentPlanPage->normalizeAmountPlans(!empty($this->request->data['AmountPlan']) ? $this->request->data['AmountPlan'] : array()) : array();
            $save = array(
                'project_id' => $postedProjectId,
                'enabled' => !empty($form['enabled']) ? 1 : 0,
                'slug' => $this->AgencyPaymentPlanPage->uniqueSlug($slugSource, $postedProjectId, $rowId),
                'page_title' => isset($form['page_title']) ? trim((string)$form['page_title']) : '',
                'hero_subtitle' => isset($form['hero_subtitle']) ? trim((string)$form['hero_subtitle']) : '',
                'unit_label' => isset($form['unit_label']) ? trim((string)$form['unit_label']) : '',
                'booking_amount' => $this->_moneyValue(isset($form['booking_amount']) ? $form['booking_amount'] : 0),
                'confirmation_amount' => $this->_moneyValue(isset($form['confirmation_amount']) ? $form['confirmation_amount'] : 0),
                'allotment_amount' => $this->_moneyValue(isset($form['allotment_amount']) ? $form['allotment_amount'] : 0),
                'monthly_installment' => $this->_moneyValue(isset($form['monthly_installment']) ? $form['monthly_installment'] : 0),
                'quarterly_installment' => $this->_moneyValue(isset($form['quarterly_installment']) ? $form['quarterly_installment'] : 0),
                'possession_amount' => $this->_moneyValue(isset($form['possession_amount']) ? $form['possession_amount'] : 0),
                'total_price' => $this->_moneyValue(isset($form['total_price']) ? $form['total_price'] : 0),
                'contact_heading' => isset($form['contact_heading']) ? trim((string)$form['contact_heading']) : '',
                'cta_label' => isset($form['cta_label']) ? trim((string)$form['cta_label']) : '',
                'meta_title' => isset($form['meta_title']) ? trim((string)$form['meta_title']) : '',
                'meta_desc' => isset($form['meta_desc']) ? trim((string)$form['meta_desc']) : '',
                'updated_by' => (int)$this->luserId
            );
            $hasAmountPlansColumn = method_exists($this->AgencyPaymentPlanPage, 'hasPaymentPlanColumn') ? $this->AgencyPaymentPlanPage->hasPaymentPlanColumn('amount_plans_json') : $this->AgencyPaymentPlanPage->hasField('amount_plans_json');
            if($hasAmountPlansColumn)
            {
                $save['amount_plans_json'] = json_encode($amountPlans);
            }
            $titleFields = array('highlights_title','summary_title','footer_title','footer_text');
            $titleDefaults = $this->_paymentPlanTitleDefaults();
            foreach($titleFields as $titleField)
            {
                $hasTitleColumn = method_exists($this->AgencyPaymentPlanPage, 'hasPaymentPlanColumn') ? $this->AgencyPaymentPlanPage->hasPaymentPlanColumn($titleField) : $this->AgencyPaymentPlanPage->hasField($titleField);
                if($hasTitleColumn)
                {
                    $titleValue = isset($form[$titleField]) ? trim((string)$form[$titleField]) : '';
                    if($titleValue === '' && isset($titleDefaults[$titleField]))
                    {
                        $titleValue = $titleDefaults[$titleField];
                    }
                    $save[$titleField] = $titleValue;
                }
            }
            if($rowId > 0)
            {
                $save['id'] = $rowId;
            }
            if($save['page_title'] === '')
            {
                $save['page_title'] = $agencyOptions[$postedProjectId].' '.__('Payment Plan');
            }
            if($save['cta_label'] === '')
            {
                $save['cta_label'] = __('Contact Agency');
            }
            $this->AgencyPaymentPlanPage->create();
            if($this->AgencyPaymentPlanPage->save(array('AgencyPaymentPlanPage' => $save), array('validate' => false)))
            {
                $savedId = !empty($this->AgencyPaymentPlanPage->id) ? (int)$this->AgencyPaymentPlanPage->id : (int)$rowId;
                if(method_exists($this->AgencyPaymentPlanPage, 'saveSettingsColumns') && !$this->AgencyPaymentPlanPage->saveSettingsColumns($savedId, $save))
                {
                    $this->Session->setFlash(__('Main payment plan was saved, but the title/footer fields could not be updated. Please try again.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array_merge($settingsRoute, array('?' => array('project_id' => $postedProjectId))));
                }
                $this->Session->setFlash(__('Agency payment plan page has been saved.'),'flash',array('alert'=>'success'));
                return $this->redirect(array_merge($settingsRoute, array('?' => array('project_id' => $postedProjectId))));
            }
            $this->Session->setFlash(__('Unable to save agency payment plan page. Please try again.'),'flash',array('alert'=>'danger'));
        }

        if(!$this->request->data)
        {
            $this->request->data = $this->_paymentPlanPageForProject($projectId);
        }
        if(!empty($this->request->data['AgencyPaymentPlanPage']))
        {
            $this->request->data['AgencyPaymentPlanPage'] = $this->_applyPaymentPlanTitleDefaults((array)$this->request->data['AgencyPaymentPlanPage']);
        }
        $pageData = !empty($this->request->data['AgencyPaymentPlanPage']) ? (array)$this->request->data['AgencyPaymentPlanPage'] : array();
        $amountPlans = $this->AgencyPaymentPlanPage->decodeJsonList(!empty($pageData['amount_plans_json']) ? $pageData['amount_plans_json'] : '');
        if(method_exists($this->AgencyPaymentPlanPage, 'normalizeAmountPlans'))
        {
            $amountPlans = $this->AgencyPaymentPlanPage->normalizeAmountPlans($amountPlans);
        }
        $publicUrl = '';
        if(!empty($pageData['slug']))
        {
            $publicUrl = Router::url('/paymentplans/'.$pageData['slug'], true);
        }
        $this->set(compact('agencyOptions','projectId','isSuperAdminSession','amountPlans','publicUrl'));
    }

    public function portalmenus()
    {
        return $this->redirect(array('controller' => 'Portalmenus', 'action' => 'index'));
    }

    public function backups($id = null)
    {
        return $this->redirect(array('controller' => 'Ardaccounts', 'action' => 'backups', $id));
    }
}
?>
