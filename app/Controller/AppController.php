<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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

App::uses('Controller', 'Controller');
App::uses('Router', 'Routing');
App::uses('Cache', 'Cache');
App::uses('Security', 'Utility');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    //public $components = array('Session','DebugKit.Toolbar','CustomFunction');
    public $components = array('Session','CustomFunction');
    public $siteTimezone,$siteName,$siteEmail,$siteDomain,$userValue,$adminValue,$frontRegistration,$frontSlides,$showReceiptSummaryCard,$showReceiptPrintDate;
    public $emailSettype, $emailHost, $emailPort, $emailUsername, $emailPassword, $emailSettingArr;
    protected function _hashPassword($plainPassword)
    {
        $plainPassword = (string)$plainPassword;
        if(function_exists('password_hash'))
        {
            return password_hash($plainPassword, PASSWORD_BCRYPT);
        }
        return Security::hash($plainPassword, 'sha256', true);
    }
    protected function _isLegacyPasswordHash($hash)
    {
        return (bool)preg_match('/^[a-f0-9]{64}$/i', trim((string)$hash));
    }
    protected function _isModernPasswordHash($hash)
    {
        $hash = trim((string)$hash);
        if($hash === '')
        {
            return false;
        }
        if(strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2b$') === 0)
        {
            return true;
        }
        if(strpos($hash, '$argon2') === 0)
        {
            return true;
        }
        return false;
    }
    protected function _verifyPassword($plainPassword, $storedHash)
    {
        $plainPassword = (string)$plainPassword;
        $storedHash = trim((string)$storedHash);
        if ($storedHash === '') {
            return false;
        }

        if ($storedHash === $plainPassword) {
            return true;
        }

        if (md5($plainPassword) === strtolower($storedHash)) {
            return true;
        }

        if ($this->_isModernPasswordHash($storedHash) && function_exists('password_verify')) {
            if (password_verify($plainPassword, $storedHash)) {
                return true;
            }
        }

        if ($this->_isLegacyPasswordHash($storedHash)) {
            if (hash_equals(strtolower($storedHash), strtolower(Security::hash($plainPassword, 'sha256', true)))) {
                return true;
            }
            if (hash_equals(strtolower($storedHash), strtolower(Security::hash($plainPassword, 'sha1', true)))) {
                return true;
            }
            if (hash_equals(strtolower($storedHash), strtolower(Security::hash($plainPassword, null, true)))) {
                return true;
            }
        }

        if (hash_equals(strtolower($storedHash), strtolower(Security::hash($plainPassword, null, true)))) {
            return true;
        }

        return false;
    }
    protected function _passwordNeedsRefresh($storedHash)
    {
        $storedHash = trim((string)$storedHash);
        if($storedHash === '')
        {
            return false;
        }
        if($this->_isLegacyPasswordHash($storedHash))
        {
            return true;
        }
        if($this->_isModernPasswordHash($storedHash) && function_exists('password_needs_rehash'))
        {
            return password_needs_rehash($storedHash, PASSWORD_BCRYPT);
        }
        return false;
    }
    protected function _rehashPasswordForModel($Model, $id, $plainPassword, $field = 'password')
    {
        if(!$Model || (int)$id <= 0 || trim((string)$field) === '')
        {
            return false;
        }
        try
        {
            $alias = !empty($Model->alias) ? $Model->alias : 'Model';
            $Model->create(false);
            return (bool)$Model->save(array(
                $alias => array(
                    'id' => (int)$id,
                    $field => $this->_hashPassword($plainPassword)
                )
            ), array('validate' => false, 'callbacks' => false, 'fieldList' => array('id', $field)));
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    protected function _normalizeTemplateVariables(array $vars)
    {
        $normalized = array();
        foreach($vars as $key => $value)
        {
            if(!is_string($key) || $key === 'this')
            {
                continue;
            }
            if(is_scalar($value) || $value === null)
            {
                $normalized[$key] = (string)$value;
            }
        }
        return $normalized;
    }
    protected function _renderTemplateString($template, array $vars = array())
    {
        $template = (string)$template;
        if($template === '')
        {
            return '';
        }
        $vars = $this->_normalizeTemplateVariables($vars);
        return preg_replace_callback('/\{\{\s*([A-Za-z_][A-Za-z0-9_]*)\s*\}\}|\{\$([A-Za-z_][A-Za-z0-9_]*)\}|\$([A-Za-z_][A-Za-z0-9_]*)/', function($matches) use ($vars) {
            $key = '';
            if(!empty($matches[1]))
            {
                $key = $matches[1];
            }
            elseif(!empty($matches[2]))
            {
                $key = $matches[2];
            }
            elseif(!empty($matches[3]))
            {
                $key = $matches[3];
            }
            if($key !== '' && array_key_exists($key, $vars))
            {
                return (string)$vars[$key];
            }
            return $matches[0];
        }, $template);
    }
    protected function _isInstallerRequest()
    {
        $controller = strtolower(isset($this->params['controller']) ? (string)$this->params['controller'] : '');
        return in_array($controller, array('installers', 'completes'), true);
    }
    protected function _hasInstallerLock()
    {
        if(file_exists(APP . 'Config' . DS . 'installed.txt') || file_exists(APP . 'installed.lock') || file_exists(APP . 'Config' . DS . 'database.php'))
        {
            if(!file_exists(TMP . 'installed.txt'))
            {
                @file_put_contents(TMP . 'installed.txt', "INSTALLED: " . date('Y-m-d H:i:s'));
            }
            return true;
        }
        if(file_exists(TMP.'installed.txt'))
        {
            return true;
        }
        $lockFiles = (array)glob(TMP.'*installed.txt');
        return !empty($lockFiles);
    }
    protected function _redirectToInstaller()
    {
        if($this->_isInstallerRequest())
        {
            return null;
        }
        $this->redirect(array('plugin'=>false,'admin'=>false,'controller'=>'installers','action'=>'index'));
        return null;
    }
    protected function _expireImpersonationSessions()
    {
        // Impersonation no longer auto-expires by time.
        // Session is restored manually via "Stop Impersonation".
    }
    protected function _syncFrontClientSession()
    {
        $frontUser = $this->Session->read('frontUser');
        $frontUser = is_array($frontUser) ? $frontUser : array();
        $changed = false;

        if(empty($frontUser['Client']) && !empty($frontUser['Contact']) && is_array($frontUser['Contact']))
        {
            $frontUser['Client'] = $frontUser['Contact'];
            $changed = true;
        }

        if(empty($frontUser['Client']['id']))
        {
            $frontImpersonation = (array)$this->Session->read('Impersonation.front');
            if(!empty($frontImpersonation['target_contact_id']))
            {
                $frontUser['Client'] = !empty($frontUser['Client']) && is_array($frontUser['Client']) ? $frontUser['Client'] : array();
                $frontUser['Client']['id'] = (int)$frontImpersonation['target_contact_id'];
                if(!empty($frontImpersonation['target_contact_name']) && empty($frontUser['Client']['name']))
                {
                    $frontUser['Client']['name'] = (string)$frontImpersonation['target_contact_name'];
                }
                $changed = true;
            }
        }

        if(!empty($frontUser['Client']['id']))
        {
            try
            {
                $this->loadModel('MyProfile');
                $clientRow = $this->MyProfile->find('first', array(
                    'conditions' => array('MyProfile.id' => (int)$frontUser['Client']['id']),
                    'recursive' => -1
                ));
                if(!empty($clientRow['MyProfile']['id']))
                {
                    $clientStatus = !empty($clientRow['MyProfile']['status']) ? trim((string)$clientRow['MyProfile']['status']) : 'Active';
                    if(strcasecmp($clientStatus, 'Active') !== 0)
                    {
                        $this->Session->delete('frontUser');
                        $this->Session->setFlash(__('Your account has been blocked. Please contact administrator to restore login access.'),'flash', array('alert'=> 'danger'));
                        $this->redirect(array('plugin' => false, 'admin' => false, 'controller' => 'Clients', 'action' => 'login'));
                        exit();
                    }
                    $frontUser['Client'] = array_merge((array)$frontUser['Client'], (array)$clientRow['MyProfile']);
                    $changed = true;
                }
            }
            catch(Exception $e)
            {
                // Keep the existing session data if the profile lookup is unavailable.
            }
        }

        if($changed)
        {
            $this->Session->write('frontUser', $frontUser);
        }
        $this->userValue = $frontUser;
        return $frontUser;
    }
    protected function _frontImageAssetUrl($asset)
    {
        $asset = trim(str_replace('\\', '/', (string)$asset), '/');
        if($asset === '')
        {
            return '';
        }
        $base = rtrim((string)Router::url('/', false), '/');
        return $base.'/img/'.$asset;
    }
    protected function _frontResolveImageFromCandidates($candidates)
    {
        foreach((array)$candidates as $candidate)
        {
            if(!is_string($candidate))
            {
                continue;
            }
            $candidate = trim(str_replace('\\', '/', $candidate), '/');
            if($candidate === '')
            {
                continue;
            }
            $filePath = WWW_ROOT.'img'.DS.str_replace('/', DS, $candidate);
            if(@file_exists($filePath))
            {
                return $this->_frontImageAssetUrl($candidate);
            }
        }
        return '';
    }
    protected function _frontResolveClientPhotoUrl($client)
    {
        $client = (array)$client;
        $photo = isset($client['photo']) ? trim((string)$client['photo']) : '';
        if($photo === '' && !empty($client['image']))
        {
            $photo = trim((string)$client['image']);
        }
        if($photo === '')
        {
            return $this->_frontResolveImageFromCandidates(array('user-icon.png','User.png'));
        }
        $dir = isset($client['dir']) ? trim((string)$client['dir']) : '';
        $dirt = isset($client['dirt']) ? trim((string)$client['dirt']) : '';
        $candidates = array(
            ($dirt !== '' ? trim($dirt, '/\\').'_thumb/'.$photo : ''),
            ($dirt !== '' ? trim($dirt, '/\\').'/'.$photo : ''),
            ($dir !== '' ? trim($dir, '/\\').'_thumb/'.$photo : ''),
            ($dir !== '' ? trim($dir, '/\\').'/'.$photo : ''),
            'contact_thumb/'.$photo,
            'contact/'.$photo,
            'client_thumb/'.$photo,
            'client/'.$photo,
            'user_thumb/'.$photo,
            'user/'.$photo,
            $photo
        );
        $resolved = $this->_frontResolveImageFromCandidates($candidates);
        if($resolved !== '')
        {
            return $resolved;
        }
        return $this->_frontResolveImageFromCandidates(array('user-icon.png','User.png'));
    }
    public function authenticate()
    {
        // Check if the session variable User exists, redirect to loginform if not
        if( $this->action != 'login' )
        {
            $frontUser = $this->_syncFrontClientSession();
            if(!$this->Session->check('frontUser') || empty($frontUser['Client']['id']))
            {
                $this->redirect(array('plugin' => false, 'admin' => false, 'controller' => 'Clients', 'action' => 'login'));
                exit();
            }
        }
    }
    public function beforeFilter()
    {
        if(!$this->_isInstallerRequest() && !$this->_hasInstallerLock())
        {
            return $this->_redirectToInstaller();
        }
        $this->_expireImpersonationSessions();
        try
        {
            $this->loadModel('Configuration');
            $this->loadModel('Currency');
            $sysSetting=$this->Configuration->find('first', array('recursive' => -1));
            if(empty($sysSetting['Configuration']))
            {
                return $this->_redirectToInstaller();
            }
            $currencyArr=$this->Currency->find('first', array(
                'conditions' => array('Currency.id' => (int)$sysSetting['Configuration']['currency']),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return $this->_redirectToInstaller();
        }
        $this->configLanguage=$sysSetting['Configuration']['language'];
        $this->siteTimezone=$sysSetting['Configuration']['timezone'];
        Configure::write('Config.language', $this->configLanguage);
        Configure::write('Config.timezone', $this->siteTimezone);
        date_default_timezone_set($this->siteTimezone);
        $this->siteDomain=$sysSetting['Configuration']['domain_name'];
        $this->siteAccount=$sysSetting['Configuration']['account_details'];
        $this->contact=$sysSetting['Configuration']['contact'];
        $this->emailNotification=$sysSetting['Configuration']['email_notification'];
        $this->smsNotification=$sysSetting['Configuration']['sms_notification'];
        $this->whatsappDemandPdf=isset($sysSetting['Configuration']['whatsapp_demand_pdf']) ? (int)$sysSetting['Configuration']['whatsapp_demand_pdf'] : 1;
        $this->currentDate=CakeTime::format('Y-m-d',time());
        $this->currentDateTime=CakeTime::format('Y-m-d H:i:s',time());
        $this->dueDays=$sysSetting['Configuration']['due_days'];
        $this->lateFees=$sysSetting['Configuration']['late_fees'];
        $this->showReceiptSummaryCard=1;
        if(isset($sysSetting['Configuration']['show_receipt_summary_card']))
        {
            $this->showReceiptSummaryCard=(int)$sysSetting['Configuration']['show_receipt_summary_card'];
        }
        $this->showReceiptPrintDate=1;
        if(isset($sysSetting['Configuration']['show_receipt_print_date']))
        {
            $this->showReceiptPrintDate=(int)$sysSetting['Configuration']['show_receipt_print_date'];
        }
        if(!isset($sysSetting['Configuration']['show_receipt_summary_card']) || !isset($sysSetting['Configuration']['show_receipt_print_date']))
        {
            try
            {
                $cfgToggleRows = $this->Configuration->query("SELECT id, show_receipt_summary_card, show_receipt_print_date FROM configurations WHERE id=1 LIMIT 1");
                $cfgToggle = array();
                if(!empty($cfgToggleRows[0]['configurations']) && is_array($cfgToggleRows[0]['configurations']))
                {
                    $cfgToggle = $cfgToggleRows[0]['configurations'];
                }
                elseif(!empty($cfgToggleRows[0][0]) && is_array($cfgToggleRows[0][0]))
                {
                    $cfgToggle = $cfgToggleRows[0][0];
                }
                if(isset($cfgToggle['show_receipt_summary_card']))
                {
                    $this->showReceiptSummaryCard = (int)$cfgToggle['show_receipt_summary_card'];
                }
                if(isset($cfgToggle['show_receipt_print_date']))
                {
                    $this->showReceiptPrintDate = (int)$cfgToggle['show_receipt_print_date'];
                }
            }
            catch(Exception $e)
            {
                // Keep defaults above.
            }
        }
        $this->set('siteTitle',$sysSetting['Configuration']['meta_title']);
        $this->set('siteDescription',$sysSetting['Configuration']['meta_desc']);
        $this->set('siteName',$sysSetting['Configuration']['name']);
        $this->set('siteOrganization',$sysSetting['Configuration']['organization_name']);
        $this->set('siteAuthorName',$sysSetting['Configuration']['author']);
        $this->set('siteYear',$sysSetting['Configuration']['created']);
        $this->set('siteAddress',$sysSetting['Configuration']['address']);
        $this->set('siteAccount',$this->siteAccount);
        $this->set('siteShort',$sysSetting['Configuration']['short_name']);
        $this->siteShort=$sysSetting['Configuration']['short_name'];
        $this->set('frontRegistration',$sysSetting['Configuration']['front_end']);
        $this->set('frontSlides',$sysSetting['Configuration']['slides']);
        $featuredTickerEnabled = 1;
        if(isset($sysSetting['Configuration']['featured_ticker_enabled']) && strlen(trim((string)$sysSetting['Configuration']['featured_ticker_enabled']))>0)
        {
            $featuredTickerEnabled = ((int)$sysSetting['Configuration']['featured_ticker_enabled'] === 1) ? 1 : 0;
        }
        $featuredTickerSpeed = 35;
        if(isset($sysSetting['Configuration']['featured_ticker_speed']) && (int)$sysSetting['Configuration']['featured_ticker_speed'] > 0)
        {
            $featuredTickerSpeed = (int)$sysSetting['Configuration']['featured_ticker_speed'];
        }
        if($featuredTickerSpeed < 10){ $featuredTickerSpeed = 10; }
        if($featuredTickerSpeed > 180){ $featuredTickerSpeed = 180; }
        $featuredTickerEffect = 'linear';
        if(isset($sysSetting['Configuration']['featured_ticker_effect']))
        {
            $candidateTickerEffect = strtolower(trim((string)$sysSetting['Configuration']['featured_ticker_effect']));
            if(in_array($candidateTickerEffect,array('linear','ease','ease-in-out')))
            {
                $featuredTickerEffect = $candidateTickerEffect;
            }
        }
        $this->set('featuredTickerEnabled',$featuredTickerEnabled);
        $this->set('featuredTickerSpeed',$featuredTickerSpeed);
        $this->set('featuredTickerEffect',$featuredTickerEffect);
        $themeDynamicCss = '';
        try
        {
            $this->loadModel('ThemeSetting');
            $themeSettings = $this->ThemeSetting->currentSettings();
            $themeDynamicCss = $this->ThemeSetting->dynamicCss($themeSettings);
        }
        catch(Exception $e)
        {
            $themeDynamicCss = '';
        }
        $this->set('themeDynamicCss', $themeDynamicCss);
        $this->set('frontLogo',$sysSetting['Configuration']['photo']);
        $this->set('siteFavicon', !empty($sysSetting['Configuration']['favicon']) ? $sysSetting['Configuration']['favicon'] : '');
        $this->set('translate',$sysSetting['Configuration']['translate']);
        $this->set('siteTimezone',$sysSetting['Configuration']['timezone']);
        $this->set('siteDomain',$sysSetting['Configuration']['domain_name']);
        $this->set('contact',$this->contact);
        $this->set('emailNotification',$this->emailNotification);
        $this->set('smsNotification',$this->smsNotification);
        $this->set('whatsappDemandPdf',$this->whatsappDemandPdf);
        $this->set('dueDays',$this->dueDays);
        $this->set('lateFees',$this->lateFees);
        $this->set('showReceiptSummaryCard',$this->showReceiptSummaryCard);
        $this->set('showReceiptPrintDate',$this->showReceiptPrintDate);
        $this->set('currentDate',$this->currentDate);
        $this->set('currentDateTime',$this->currentDateTime);
        $currencySymbol='';
        if(isset($currencyArr['Currency']['symbol']) && strlen(trim((string)$currencyArr['Currency']['symbol']))>0)
        {
            $currencySymbol=trim($currencyArr['Currency']['symbol']);
        }
        elseif(isset($currencyArr['Currency']['photo']) && strlen(trim((string)$currencyArr['Currency']['photo']))>0)
        {
            $legacyPhotoValue = trim($currencyArr['Currency']['photo']);
            if(!preg_match('/\.(jpg|jpeg|png|gif|bmp|svg)$/i',$legacyPhotoValue))
            {
                $currencySymbol=$legacyPhotoValue;
            }
        }
        if(strlen($currencySymbol)<=0 && isset($currencyArr['Currency']['short']) && strlen(trim((string)$currencyArr['Currency']['short']))>0)
        {
            $currencySymbol=trim($currencyArr['Currency']['short']);
        }
        if(strlen($currencySymbol)<=0)
        {
            $currencySymbol='Rs';
        }
        $currency=$currencySymbol.' ';
        $this->currency=$currency;
        $this->currencyFull=$currency;
        $this->currencyType=(isset($currencyArr['Currency']['short']) && strlen(trim((string)$currencyArr['Currency']['short']))>0) ? trim($currencyArr['Currency']['short']) : $currencySymbol;
        $this->currencyName=$currencySymbol;
        $this->set('currency',$currency);
        $this->set('currencyType',$this->currencyType);
        $this->emiArr=array('M'=>__('Monthly'),'Q'=>__('Quarterly'),'S'=>__('Semi Annually'),'A'=>__('Annually'));
        $this->set('emiArr',$this->emiArr);
        $sysDateArr=explode(",",$sysSetting['Configuration']['date_format']);
        if(count($sysDateArr)<9)
        {
            $sysDateArr=array('d','M','Y','h','i','s','A','-',':');
        }
        $this->sysDay=$sysDateArr[0];$this->sysMonth=$sysDateArr[1];$this->sysYear=$sysDateArr[2];
        $this->sysHour=$sysDateArr[3];$this->sysMin=$sysDateArr[4];$this->sysSec=$sysDateArr[5];$this->sysMer=$sysDateArr[6];
        $this->set('sysDay',$this->sysDay);$this->set('sysMonth',$this->sysMonth);$this->set('sysYear',$this->sysYear);
        $this->set('sysHour',$this->sysHour);$this->set('sysMin',$this->sysMin);$this->set('sysSec',$this->sysSec);$this->set('sysMer',$this->sysMer);
        $this->dateSep=$sysDateArr[7];$this->timeSep=$sysDateArr[8];$this->dateGap=" ";
        $this->set('dateSep',$this->dateSep);$this->set('timeSep',$this->timeSep);$this->set('dateGap',$this->dateGap);
        $this->dtFormat=null;
        $pickerTokenMap=array('d'=>'DD','j'=>'D','m'=>'MM','n'=>'M','M'=>'MMM','F'=>'MMMM','Y'=>'YYYY','y'=>'YY');
        $displayTokens=array($this->sysDay,$this->sysMonth,$this->sysYear);
        $pickerTokens=array();
        foreach($displayTokens as $displayToken)
        {
            if(!isset($pickerTokenMap[$displayToken]))
            {
                $pickerTokens=array();
                break;
            }
            $pickerTokens[]=$pickerTokenMap[$displayToken];
        }
        if(empty($pickerTokens))
        {
            $this->dpFormat="DD-MMM-YYYY";
            $this->dtFormat="d-M-Y";
        }
        else
        {
            $this->dpFormat=implode($this->dateSep,$pickerTokens);
            $this->dtFormat=implode($this->dateSep,$displayTokens);
        }
        $this->set('dpFormat', $this->dpFormat);
        $this->set('dtFormat', $this->dtFormat);
        if($sysSetting['Configuration']['min_limit'])
        $minLimit=$sysSetting['Configuration']['min_limit'];
        else
        $minLimit=20;
        if($sysSetting['Configuration']['max_limit'])
        $maxLimit=$sysSetting['Configuration']['max_limit'];
        else
        $maxLimit=500;
        $this->pageLimit=$minLimit;
        $this->maxLimit=$maxLimit;
        $this->siteName=$sysSetting['Configuration']['name'];
       
        $this->siteEmail=$sysSetting['Configuration']['email'];
        $this->frontRegistration=$sysSetting['Configuration']['front_end'];
        $this->frontSlides=$sysSetting['Configuration']['slides'];
        $this->mapKey=$sysSetting['Configuration']['map_key'];
        $this->set('mapKey',$this->mapKey);
        $this->defaultMapProvider='osm';
        if(isset($sysSetting['Configuration']['default_map_provider']))
        {
            $candidateProvider=strtolower(trim((string)$sysSetting['Configuration']['default_map_provider']));
            if(in_array($candidateProvider,array('osm','google')))
            {
                $this->defaultMapProvider=$candidateProvider;
            }
        }
        $this->set('defaultMapProvider',$this->defaultMapProvider);
        if($sysSetting['Configuration']['captcha_type']==1)
        $this->captchaType="image";
        else
        $this->captchaType="math";
        $this->set('captchaType',$this->captchaType);
        $menuArr=array(array('Page'=>array('model_name'=>__('Dashboard'),'controller_name'=>'Dashboards','action_name'=>'index','icon'=>'fa fa-dashboard','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('My Deals'),'controller_name'=>'MyDeals','action_name'=>'index','icon'=>'fa fa-thumbs-up','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('Utility Bills'),'controller_name'=>'UtilityBills','action_name'=>'index','icon'=>'fa fa-bolt','sel_name'=>'utilitybills,utility_bills,myutilitybills,my_utility_bills,myutilitybill,billingportals')),
                       array('Page'=>array('model_name'=>__('My Payments'),'controller_name'=>'MyPayments','action_name'=>'index','icon'=>'fa fa-dollar','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('Overdue Installments'),'controller_name'=>'MyInvpastdues','action_name'=>'index','icon'=>'fa fa-thumb-tack','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('My Documents'),'controller_name'=>'MyDocuments','action_name'=>'index','icon'=>'fa fa-lock','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('My Rentals'),'controller_name'=>'MyRentals','action_name'=>'index','icon'=>'fa fa-key','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('Refund Requests'),'controller_name'=>'MyRefundrequests','action_name'=>'index','icon'=>'fa fa-undo','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('Search Properties'),'controller_name'=>'Properties','action_name'=>'index','icon'=>'fa fa-building','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('Favourite Properties'),'controller_name'=>'Favouriteproperties','action_name'=>'index','icon'=>'fa fa-heart','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('My Profile'),'controller_name'=>'MyProfiles','action_name'=>'index','icon'=>'fa fa-user','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('Change Password'),'controller_name'=>'Changepasswords','action_name'=>'index','icon'=>'fa fa-cog','sel_name'=>'')),
                       array('Page'=>array('model_name'=>__('Signout'),'controller_name'=>'Clients','action_name'=>'logout','icon'=>'fa fa-power-off','sel_name'=>'')),
                       );
        $frontmenuArr=array("Dashboard"=>array('model_name'=>__('Dashboard'),'controller'=>'Dashboards','action'=>'index','icon'=>'fa fa-dashboard','sel_name'=>''));
        $this->set('menuArr',$menuArr);
        $this->set('siteEmail',$this->siteEmail);
        $this->set('frontmenuArr',$frontmenuArr);
        if($sysSetting['Configuration']['dir_type']==1)
        $this->dirType="ltr";
        else
        $this->dirType="rtl";
        $this->set('dirType',$this->dirType);
        $this->set('captchaType',$this->captchaType);
        $this->set('configLanguage',$this->configLanguage);
        $frontUser = $this->_syncFrontClientSession();
        $clientId = !empty($frontUser['Client']['id']) ? (int)$frontUser['Client']['id'] : 0;
        
        $this->set('UserArr', $frontUser);
        $this->set('userValue', $frontUser);
        $this->set('frontUser', $frontUser);

        if($clientId > 0)
        {
            if(!$this->_frontClientHasRentalAccess($clientId))
            {
                foreach($menuArr as $menuIndex => $menuItem)
                {
                    if(!empty($menuItem['Page']['controller_name']) && strcasecmp($menuItem['Page']['controller_name'], 'MyRentals') === 0)
                    {
                        unset($menuArr[$menuIndex]);
                    }
                }
                $menuArr = array_values($menuArr);
            }

            // Check if client is connected to Utility Bills & Meters
            $frontBillingAccess = $this->_getFrontClientBillingAccess($clientId, isset($frontUser['Client']) ? $frontUser['Client'] : array());
            $isCurrentBillingPage = in_array(strtolower($this->params['controller']), array('utilitybills', 'utility_bills', 'myutilitybills', 'my_utility_bills', 'billingportals'));
            
            if(empty($frontBillingAccess['has_access']) && !$isCurrentBillingPage)
            {
                foreach($menuArr as $menuIndex => $menuItem)
                {
                    if(!empty($menuItem['Page']['controller_name']) && (strcasecmp($menuItem['Page']['controller_name'], 'UtilityBills') === 0 || strcasecmp($menuItem['Page']['controller_name'], 'MyUtilityBills') === 0))
                    {
                        unset($menuArr[$menuIndex]);
                    }
                }
                $menuArr = array_values($menuArr);
            }
            $this->set('frontBillingAccess', $frontBillingAccess);
        }
        $this->set('menuArr', $menuArr);

        $this->set('frontClientPhotoUrl', $this->_frontResolveClientPhotoUrl(isset($frontUser['Client']) ? $frontUser['Client'] : array()));
        $this->adminValue=$this->Session->read('User');
        $this->loadModel('Content');
        $contents=array();
       
        $contents=$this->Content->find('all',array(
                                            'fields'=>array('Content.link_name','Content.is_url','Content.url','Content.page_url'),
                                            'conditions'=>array('Content.published'=>'Published'),
                                            'order'=>array('Content.ordering'=>'asc'),
                                            'recursive' => -1));
        $this->set('contents',$contents);
        $this->set('contentId','');
        $this->emailSettings();
        $this->smsSettings();
    }

    public function _getFrontClientBillingAccess($clientId, $clientData = array())
    {
        $clientId = (int)$clientId;
        if($clientId <= 0)
        {
            return array('has_access' => false);
        }

        try
        {
            $BillingClient = ClassRegistry::init('BillingManager.BillingClient');
            if(!$BillingClient)
            {
                return array('has_access' => false);
            }

            $conds = array(
                'OR' => array(
                    'BillingClient.app_client_id' => $clientId
                )
            );

            if(!empty($clientData['email']))
            {
                $conds['OR']['BillingClient.email'] = trim($clientData['email']);
            }
            if(!empty($clientData['phone']))
            {
                $conds['OR']['BillingClient.phone'] = trim($clientData['phone']);
            }
            if(!empty($clientData['mobile']))
            {
                $conds['OR']['BillingClient.phone'] = trim($clientData['mobile']);
            }

            $bClient = $BillingClient->find('first', array(
                'conditions' => $conds,
                'order' => array('BillingClient.id' => 'ASC'),
                'recursive' => -1
            ));

            if(empty($bClient['BillingClient']['id']))
            {
                return array('has_access' => false);
            }

            $bClientId = (int)$bClient['BillingClient']['id'];
            
            $BillingBill = ClassRegistry::init('BillingManager.BillingBill');
            $billsCount = $BillingBill->find('count', array(
                'conditions' => array('BillingBill.billing_client_id' => $bClientId),
                'recursive' => -1
            ));

            $BillingProperty = ClassRegistry::init('BillingManager.BillingProperty');
            $propsCount = $BillingProperty->find('count', array(
                'conditions' => array('BillingProperty.billing_client_id' => $bClientId),
                'recursive' => -1
            ));

            $hasAccess = ($billsCount > 0 || $propsCount > 0);

            return array(
                'has_access' => $hasAccess,
                'billing_client_id' => $bClientId,
                'billing_client' => $bClient['BillingClient'],
                'bills_count' => $billsCount,
                'props_count' => $propsCount
            );
        }
        catch(Exception $e)
        {
            return array('has_access' => false);
        }
    }

    protected function _frontClientHasRentalAccess($clientId)
    {
        $clientId = (int)$clientId;
        if($clientId <= 0)
        {
            return false;
        }
        try
        {
            $this->loadModel('Admin.DealRental');
            return (int)$this->DealRental->find('count', array(
                'conditions' => array(
                    'DealRental.client_id' => $clientId,
                    'DealRental.client_portal_access' => 'Active'
                ),
                'recursive' => -1
            )) > 0;
        }
        catch(Exception $e)
        {
            return true;
        }
    }
    /* Email Setting */
    public function emailSettings()
    {
        $this->loadModel('Emailsetting');
        $emailSettingArr = $this->Emailsetting->find('first', array(
            'conditions' => array('Emailsetting.id' => 1),
            'recursive' => -1
        ));
        $this->emailSettingArr = $emailSettingArr;
        $this->emailSettype = !empty($emailSettingArr['Emailsetting']['type']) ? $emailSettingArr['Emailsetting']['type'] : 'Smtp';
        $this->emailHost = !empty($emailSettingArr['Emailsetting']['host']) ? $emailSettingArr['Emailsetting']['host'] : '';
        $this->emailPort = !empty($emailSettingArr['Emailsetting']['port']) ? (int)$emailSettingArr['Emailsetting']['port'] : 465;
        $this->emailUsername = !empty($emailSettingArr['Emailsetting']['username']) ? $emailSettingArr['Emailsetting']['username'] : '';
        $this->emailPassword = !empty($emailSettingArr['Emailsetting']['password']) ? $emailSettingArr['Emailsetting']['password'] : '';
    }
    /* End Email Settings */
    /* Email Setting */
    public function smsSettings()
    {
        $this->loadModel('Smssetting');
        $smsSettingArr = $this->Smssetting->find('first', array(
            'conditions' => array('Smssetting.id' => 1),
            'recursive' => -1
        ));
        $this->smsSettingArr = $smsSettingArr;
    }
    /* End Email Settings */

    /**
     * Universal System Email Dispatcher
     * Safely handles SSL (465), TLS (587), Exim sender verification, and HTML formatting across all controllers.
     */
    public function sendSystemEmail($toEmail, $subject, $message, $template = 'default', $customVars = array())
    {
        try
        {
            if(empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL))
            {
                CakeLog::write('warning', "sendSystemEmail skipped: Invalid recipient email '{$toEmail}'");
                return false;
            }

            $this->emailSettings();
            App::uses('CakeEmail', 'Network/Email');
            $Email = new CakeEmail();

            $transport = (!empty($this->emailSettype)) ? (strtolower($this->emailSettype) === 'smtp' ? 'Smtp' : 'Mail') : 'Smtp';
            $Email->transport($transport);

            $fromAddress = !empty($this->siteEmail) ? $this->siteEmail : 'info@whitevillas.com.pk';
            $fromName = !empty($this->siteName) ? $this->siteName : 'White Villas';

            if($transport === 'Smtp')
            {
                $port = !empty($this->emailPort) ? (int)$this->emailPort : 465;
                $host = !empty($this->emailHost) ? trim($this->emailHost) : 'localhost';
                $username = !empty($this->emailUsername) ? trim($this->emailUsername) : '';
                $password = !empty($this->emailPassword) ? $this->emailPassword : '';

                $smtpConfig = array(
                    'host' => $host,
                    'port' => $port,
                    'username' => $username,
                    'password' => $password,
                    'timeout' => 25
                );

                if($port === 465 || strpos($host, 'ssl://') === 0)
                {
                    $smtpConfig['protocol'] = 'ssl';
                    $smtpConfig['ssl_allow_self_signed'] = true;
                    $smtpConfig['context'] = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                }
                elseif($port === 587)
                {
                    $smtpConfig['tls'] = true;
                }

                $Email->config($smtpConfig);

                $actualFrom = (!empty($username) && filter_var($username, FILTER_VALIDATE_EMAIL)) ? $username : $fromAddress;
                $Email->from(array($actualFrom => $fromName));
                if(!empty($fromAddress) && strtolower($fromAddress) !== strtolower($actualFrom))
                {
                    $Email->replyTo($fromAddress, $fromName);
                }
            }
            else
            {
                $Email->from(array($fromAddress => $fromName));
            }

            $Email->to($toEmail);
            $Email->template($template);
            $Email->emailFormat('html');

            $cleanSubject = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', (string)$subject);
            $cleanSubject = trim(preg_replace('/\s+/', ' ', (string)$cleanSubject));
            $Email->subject($cleanSubject !== '' ? $cleanSubject : 'System Notification');

            if(!empty($customVars) && is_array($customVars))
            {
                $Email->viewVars($customVars);
            }

            $Email->send($message);
            CakeLog::write('info', "sendSystemEmail SUCCESS to {$toEmail} | Subject: {$cleanSubject}");
            return true;
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', "sendSystemEmail failed to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Universal System SMS Dispatcher
     * Dynamically initializes CustomFunction, validates format, and handles network gracefully.
     */
    public function sendSystemSms($toMobile, $message)
    {
        try
        {
            $cleanMobile = preg_replace('/[^0-9]/', '', (string)$toMobile);
            if(empty($cleanMobile) || strlen($cleanMobile) < 10)
            {
                CakeLog::write('warning', "sendSystemSms skipped: Invalid phone '{$toMobile}'");
                return false;
            }

            $this->smsSettings();
            if(empty($this->smsSettingArr['Smssetting']))
            {
                CakeLog::write('warning', "sendSystemSms skipped: No SMS settings configured");
                return false;
            }

            if(!isset($this->CustomFunction))
            {
                App::uses('ComponentCollection', 'Controller');
                App::uses('CustomFunctionComponent', 'Controller/Component');
                $this->CustomFunction = new CustomFunctionComponent(new ComponentCollection());
            }

            $res = $this->CustomFunction->sendSms($cleanMobile, $message, $this->smsSettingArr);
            CakeLog::write('info', "sendSystemSms SUCCESS to {$cleanMobile}");
            return true;
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', "sendSystemSms failed to {$toMobile}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Global Theme Module Layout Integration
     * Safely applies RealEstateTheme layout across all frontend pages when active.
     * When disabled, reverts automatically to default view layout.
     */
    public function beforeRender()
    {
        parent::beforeRender();
        $this->_applyThemeModuleLayout();
    }

    protected function _applyThemeModuleLayout()
    {
        // 1. Skip admin prefix, admin plugin, installers, ajax, or null/false layouts
        if (!empty($this->params['admin']) || (!empty($this->params['prefix']) && $this->params['prefix'] === 'admin')) {
            return;
        }
        if (!empty($this->params['plugin']) && strcasecmp($this->params['plugin'], 'Admin') === 0) {
            return;
        }
        if ($this->_isInstallerRequest()) {
            return;
        }
        if ($this->layout === false || $this->layout === null || $this->layout === 'ajax' || $this->request->is('ajax')) {
            return;
        }

        // 2. Skip logged-in client portal internal dashboard pages
        $controllerName = strtolower(isset($this->params['controller']) ? (string)$this->params['controller'] : (!empty($this->name) ? strtolower((string)$this->name) : ''));
        $clientPortalControllers = array(
            'dashboards', 'mydeals', 'my_deals', 'mypayments', 'my_payments',
            'myinvpastdues', 'my_invpastdues', 'mydocuments', 'my_documents',
            'myrentals', 'my_rentals', 'myrefundrequests', 'my_refundrequests',
            'myprofiles', 'my_profiles', 'changepasswords', 'change_passwords',
            'utilitybills', 'utility_bills', 'myutilitybills', 'my_utility_bills', 'myutilitybill', 'my_utility_bill'
        );
        $isPortalCtrl = in_array($controllerName, $clientPortalControllers, true) || (strpos($controllerName, 'my') === 0) || (strpos($controllerName, 'utility') === 0);
        if ($isPortalCtrl) {
            return;
        }

        // 3. Dynamically check if RealEstateTheme module is enabled
        try {
            if (CakePlugin::loaded('RealEstateTheme')) {
                $this->loadModel('SystemModule');
                $themeMod = $this->SystemModule->find('first', array(
                    'conditions' => array('SystemModule.slug' => 'RealEstateTheme', 'SystemModule.status' => 1),
                    'recursive' => -1
                ));
                if (!empty($themeMod)) {
                    $this->loadModel('RealEstateTheme.RealEstateThemeSetting');
                    $themeSettings = $this->RealEstateThemeSetting->find('first', array('conditions' => array('id' => 1)));
                    $this->set('themeSettings', !empty($themeSettings['RealEstateThemeSetting']) ? $themeSettings['RealEstateThemeSetting'] : array());
                    $this->layout = 'RealEstateTheme.real_estate_theme';
                }
            }
        } catch (Exception $e) {
            // Silently maintain default layout
        }
    }
}
?>
