<?php
App::uses('Router', 'Routing');
App::uses('CakeEmail', 'Network/Email');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
class CrmintegrationsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator');
    public $components = array('Session', 'Paginator');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('CrmIntegration');
        $this->loadModel('CrmGoogleCalendar');
        if($this->_shouldRunControllerBootstrap('crmintegrations.schema', '2026-04-09.2'))
        {
            $this->CrmIntegration->ensureSchema();
            $this->CrmGoogleCalendar->ensureSchema();
            $this->_ensureCrmIntegrationMenu();
        }
    }

    protected function _providerOptions()
    {
        return (array)$this->CrmIntegration->providerOptions();
    }

    protected function _settingsTabOptions()
    {
        return array_merge(
            array(
                'sms_gateway' => __('SMS'),
                'email_gateway' => __('E-mail'),
                'google_calendar' => __('Google Calendar')
            ),
            (array)$this->_providerOptions()
        );
    }

    protected function _currentAdminUserId()
    {
        $sessionUser = (array)$this->Session->read('User');
        return !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
    }

    protected function _routeTypeOptionsMap()
    {
        return array(
            'facebook_leads' => array(
                'form_id' => __('Facebook Form ID'),
                'page_id' => __('Facebook Page ID')
            ),
            'whatsapp_cloud' => array(
                'phone_number_id' => __('WhatsApp Phone Number ID')
            )
        );
    }

    protected function _routeTypeOptions($provider = '')
    {
        $provider = strtolower(trim((string)$provider));
        $map = $this->_routeTypeOptionsMap();
        if($provider !== '' && isset($map[$provider]))
        {
            return (array)$map[$provider];
        }
        $flat = array();
        foreach($map as $providerRows)
        {
            foreach((array)$providerRows as $typeKey => $typeLabel)
            {
                $flat[$typeKey] = $typeLabel;
            }
        }
        return $flat;
    }

    protected function _projectOptions()
    {
        return (array)$this->CustomFunction->agencyWiseList($this->userType, $this->agencyId);
    }

    protected function _userLevelOptions()
    {
        $this->loadModel('User');
        $this->loadModel('Ugroup');
        $groupIds = array_values(array_unique(array_map('intval', (array)$this->User->assignableGroupIds())));
        if(empty($groupIds))
        {
            return array();
        }
        return (array)$this->Ugroup->find('list', array(
            'fields' => array('Ugroup.id', 'Ugroup.name'),
            'conditions' => array('Ugroup.id' => $groupIds),
            'order' => array('Ugroup.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _userOptions($projectId = 0, $ugroupId = 0)
    {
        $this->loadModel('User');
        $projectId = (int)$projectId;
        $ugroupId = (int)$ugroupId;
        $conditions = array($this->User->assignableScopeConditions($projectId, 'User'));
        if($ugroupId > 0)
        {
            $conditions[] = array('User.ugroup_id' => $ugroupId);
        }
        return (array)$this->User->find('list', array(
            'fields' => array('User.id', 'User.name'),
            'conditions' => $conditions,
            'order' => array('User.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _normalizePositiveId($value)
    {
        $value = (int)$value;
        return ($value > 0) ? $value : 0;
    }

    protected function _normalizeEnabledFlag($value)
    {
        return !empty($value) ? 1 : 0;
    }

    protected function _googleCalendarCallbackUrl()
    {
        return Router::url(array('controller' => 'Crmintegrations', 'action' => 'googlecalendarcallback', 'admin' => true), true);
    }

    protected function _providerWebhookUrl($provider)
    {
        $provider = strtolower(trim((string)$provider));
        if($provider === 'facebook_leads')
        {
            return Router::url(array('plugin' => false, 'admin' => false, 'controller' => 'crmwebhooks', 'action' => 'facebook'), true);
        }
        if($provider === 'whatsapp_cloud')
        {
            return Router::url(array('plugin' => false, 'admin' => false, 'controller' => 'crmwebhooks', 'action' => 'whatsapp'), true);
        }
        return '';
    }

    protected function _normalizeUserSmsSettingsData($rawData = array())
    {
        $rawData = (array)$rawData;
        if(!empty($rawData['data']) && is_array($rawData['data']))
        {
            $rawData = array_merge((array)$rawData['data'], $rawData);
        }
        return array(
            'use_global' => $this->_normalizeEnabledFlag(isset($rawData['use_global']) ? $rawData['use_global'] : 0),
            'default_sms_template_id' => $this->_normalizePositiveId(isset($rawData['default_sms_template_id']) ? $rawData['default_sms_template_id'] : (isset($rawData['default_template_id']) ? $rawData['default_template_id'] : 0)),
            'api' => trim((string)(isset($rawData['api']) ? $rawData['api'] : '')),
            'senderid' => trim((string)(isset($rawData['senderid']) ? $rawData['senderid'] : '')),
            'username' => trim((string)(isset($rawData['username']) ? $rawData['username'] : '')),
            'password' => trim((string)(isset($rawData['password']) ? $rawData['password'] : '')),
            'husername' => trim((string)(isset($rawData['husername']) ? $rawData['husername'] : '')),
            'hpassword' => trim((string)(isset($rawData['hpassword']) ? $rawData['hpassword'] : '')),
            'hmobile' => trim((string)(isset($rawData['hmobile']) ? $rawData['hmobile'] : '')),
            'hmessage' => trim((string)(isset($rawData['hmessage']) ? $rawData['hmessage'] : '')),
            'hsenderid' => trim((string)(isset($rawData['hsenderid']) ? $rawData['hsenderid'] : '')),
            'type' => (strtolower(trim((string)(isset($rawData['type']) ? $rawData['type'] : 'get'))) === 'post') ? 'Post' : 'Get'
        );
    }

    protected function _normalizeUserEmailSettingsData($rawData = array())
    {
        $rawData = (array)$rawData;
        if(!empty($rawData['data']) && is_array($rawData['data']))
        {
            $rawData = array_merge((array)$rawData['data'], $rawData);
        }
        $emailType = strtolower(trim((string)(isset($rawData['type']) ? $rawData['type'] : 'mail')));
        $emailType = ($emailType === 'smtp') ? 'Smtp' : 'Mail';
        $row = array(
            'use_global' => $this->_normalizeEnabledFlag(isset($rawData['use_global']) ? $rawData['use_global'] : 0),
            'default_email_template_id' => $this->_normalizePositiveId(isset($rawData['default_email_template_id']) ? $rawData['default_email_template_id'] : (isset($rawData['default_template_id']) ? $rawData['default_template_id'] : 0)),
            'type' => $emailType,
            'host' => trim((string)(isset($rawData['host']) ? $rawData['host'] : '')),
            'port' => trim((string)(isset($rawData['port']) ? $rawData['port'] : '')),
            'username' => trim((string)(isset($rawData['username']) ? $rawData['username'] : '')),
            'password' => trim((string)(isset($rawData['password']) ? $rawData['password'] : ''))
        );
        if($emailType !== 'Smtp')
        {
            $row['host'] = '';
            $row['port'] = '';
            $row['username'] = '';
            $row['password'] = '';
        }
        return $row;
    }

    protected function _postedNestedValue($rawData = array(), $fieldName = '', $defaultValue = null)
    {
        $rawData = (array)$rawData;
        $fieldName = trim((string)$fieldName);
        if($fieldName === '')
        {
            return $defaultValue;
        }
        if(!empty($rawData['data']) && is_array($rawData['data']) && array_key_exists($fieldName, $rawData['data']))
        {
            return $rawData['data'][$fieldName];
        }
        if(array_key_exists($fieldName, $rawData))
        {
            return $rawData[$fieldName];
        }
        return $defaultValue;
    }

    protected function _leadTemplateBundle($mode = 'sms', $userId = 0)
    {
        $mode = strtolower(trim((string)$mode));
        $isEmail = ($mode === 'email');
        $modelName = $isEmail ? 'Emailtemplate' : 'Smstemplate';
        $defaultTypes = $isEmail ? array('LED', 'LDE') : array('LED', 'LDS');
        $userId = (int)$userId;
        if($userId <= 0)
        {
            $userId = $this->_currentAdminUserId();
        }
        $isSuperAdmin = $this->_isSuperAdminSession();
        $Model = ClassRegistry::init('Admin.'.$modelName);
        if(!$Model)
        {
            return array();
        }
        if(method_exists($Model, 'ensureOwnershipSchema'))
        {
            $Model->ensureOwnershipSchema();
        }
        $customConditions = method_exists($Model, 'scopedCustomConditions')
            ? $Model->scopedCustomConditions($userId, $isSuperAdmin, $modelName)
            : array(
                'OR' => array(
                    array($modelName.'.type' => null),
                    array($modelName.'.type' => '')
                )
            );
        $rows = (array)$Model->find('all', array(
            'conditions' => array(
                $modelName.'.status' => 'Published',
                'OR' => array(
                    array($modelName.'.type' => $defaultTypes),
                    $customConditions
                )
            ),
            'fields' => array($modelName.'.id', $modelName.'.name', $modelName.'.type'),
            'order' => array($modelName.'.name' => 'asc'),
            'recursive' => -1
        ));

        $options = array();
        $customRows = array();
        $defaultRows = array();
        foreach($rows as $row)
        {
            if(empty($row[$modelName]['id']) || trim((string)$row[$modelName]['name']) === '')
            {
                continue;
            }
            $templateId = (int)$row[$modelName]['id'];
            $templateName = trim((string)$row[$modelName]['name']);
            $templateType = strtoupper(trim((string)$row[$modelName]['type']));
            if(in_array($templateType, $defaultTypes, true))
            {
                $defaultRows[$templateId] = $templateName.' ('.$templateType.')';
            }
            else
            {
                $customRows[$templateId] = $templateName;
            }
        }
        if(!empty($customRows))
        {
            $options[__('My Templates')] = $customRows;
        }
        if(!empty($defaultRows))
        {
            $options[__('Lead Default Template')] = $defaultRows;
        }
        return $options;
    }

    protected function _templateOptionExists($options = array(), $needleId = 0)
    {
        $needleId = (int)$needleId;
        if($needleId <= 0)
        {
            return false;
        }
        foreach((array)$options as $key => $value)
        {
            if(is_array($value))
            {
                if($this->_templateOptionExists($value, $needleId))
                {
                    return true;
                }
                continue;
            }
            if((int)$key === $needleId)
            {
                return true;
            }
        }
        return false;
    }

    protected function _appendSavedTemplateOption($options = array(), $mode = 'sms', $templateId = 0, $userId = 0)
    {
        $templateId = (int)$templateId;
        $userId = (int)$userId;
        if($templateId <= 0 || $this->_templateOptionExists($options, $templateId))
        {
            return (array)$options;
        }

        $mode = strtolower(trim((string)$mode));
        $isEmail = ($mode === 'email');
        $modelName = $isEmail ? 'Emailtemplate' : 'Smstemplate';
        $defaultTypes = $isEmail ? array('LED', 'LDE') : array('LED', 'LDS');
        $isSuperAdmin = $this->_isSuperAdminSession();
        $Model = ClassRegistry::init('Admin.'.$modelName);
        if(!$Model)
        {
            return (array)$options;
        }
        if(method_exists($Model, 'ensureOwnershipSchema'))
        {
            $Model->ensureOwnershipSchema();
        }

        $row = (array)$Model->find('first', array(
            'conditions' => array(
                $modelName.'.id' => $templateId,
                $modelName.'.status' => 'Published'
            ),
            'fields' => array($modelName.'.id', $modelName.'.name', $modelName.'.type', $modelName.'.user_id'),
            'recursive' => -1
        ));
        if(empty($row[$modelName]['id']))
        {
            return (array)$options;
        }

        $templateType = strtoupper(trim((string)$row[$modelName]['type']));
        $ownerId = !empty($row[$modelName]['user_id']) ? (int)$row[$modelName]['user_id'] : 0;
        if(!$isSuperAdmin && !in_array($templateType, $defaultTypes, true) && $userId > 0 && $ownerId !== $userId)
        {
            return (array)$options;
        }

        $templateName = trim((string)$row[$modelName]['name']);
        if($templateName === '')
        {
            return (array)$options;
        }

        if(in_array($templateType, $defaultTypes, true))
        {
            if(!isset($options[__('Lead Default Template')]) || !is_array($options[__('Lead Default Template')]))
            {
                $options[__('Lead Default Template')] = array();
            }
            $options[__('Lead Default Template')][$templateId] = $templateName.' ('.$templateType.')';
            return (array)$options;
        }

        if(!isset($options[__('My Templates')]) || !is_array($options[__('My Templates')]))
        {
            $options[__('My Templates')] = array();
        }
        $options[__('My Templates')][$templateId] = $templateName;
        return (array)$options;
    }

    protected function _normalizeGoogleCalendarBaseData($rawData = array())
    {
        $rawData = (array)$rawData;
        $calendarId = trim((string)(isset($rawData['calendar_id']) ? $rawData['calendar_id'] : ''));
        if($calendarId === '')
        {
            $calendarId = 'primary';
        }
        return array(
            'client_id' => trim((string)(isset($rawData['client_id']) ? $rawData['client_id'] : '')),
            'client_secret' => trim((string)(isset($rawData['client_secret']) ? $rawData['client_secret'] : '')),
            'calendar_id' => $calendarId
        );
    }

    protected function _normalizeUserGoogleCalendarSettingsData($rawData = array())
    {
        $row = $this->_normalizeGoogleCalendarBaseData((array)$rawData);
        $row['use_global'] = $this->_normalizeEnabledFlag(isset($rawData['use_global']) ? $rawData['use_global'] : 0);
        $row['sync_enabled'] = $this->_normalizeEnabledFlag(isset($rawData['sync_enabled']) ? $rawData['sync_enabled'] : 0);
        $row['sync_follow_up'] = $this->_normalizeEnabledFlag(isset($rawData['sync_follow_up']) ? $rawData['sync_follow_up'] : 0);
        $row['sync_calendar_tasks'] = $this->_normalizeEnabledFlag(isset($rawData['sync_calendar_tasks']) ? $rawData['sync_calendar_tasks'] : 0);
        return $row;
    }

    protected function _smsGatewayLooksConfigured($smsSettings = array())
    {
        $smsRow = !empty($smsSettings['Smssetting']) ? (array)$smsSettings['Smssetting'] : (array)$smsSettings;
        return (trim((string)(isset($smsRow['api']) ? $smsRow['api'] : '')) !== '');
    }

    protected function _emailGatewayLooksConfigured($emailSettings = array())
    {
        $emailRow = !empty($emailSettings['Emailsetting']) ? (array)$emailSettings['Emailsetting'] : (array)$emailSettings;
        $type = strtolower(trim((string)(isset($emailRow['type']) ? $emailRow['type'] : 'mail')));
        if($type === 'smtp')
        {
            return (
                trim((string)(isset($emailRow['host']) ? $emailRow['host'] : '')) !== '' &&
                trim((string)(isset($emailRow['port']) ? $emailRow['port'] : '')) !== ''
            );
        }
        return true;
    }

    protected function _sendGatewayTestSms($smsSettings = array(), $mobileNo = '', $message = '')
    {
        $mobileNo = trim((string)$mobileNo);
        $message = trim((string)$message);
        if($mobileNo === '' || $message === '')
        {
            throw new Exception(__('Please enter test mobile number and message.'));
        }
        if(!$this->_smsGatewayLooksConfigured($smsSettings))
        {
            throw new Exception(__('SMS gateway is not configured yet.'));
        }
        $this->CustomFunction->sendSms($mobileNo, $message, $smsSettings);
        return true;
    }

    protected function _sendGatewayTestEmail($emailSettings = array(), $toEmail = '', $subject = '', $message = '')
    {
        $toEmail = trim((string)$toEmail);
        $subject = trim((string)$subject);
        $message = trim((string)$message);
        if($toEmail === '' || $subject === '' || $message === '')
        {
            throw new Exception(__('Please enter test email, subject and message.'));
        }
        if(!$this->_emailGatewayLooksConfigured($emailSettings))
        {
            throw new Exception(__('Email gateway is not configured yet.'));
        }
        $emailRow = !empty($emailSettings['Emailsetting']) ? (array)$emailSettings['Emailsetting'] : (array)$emailSettings;
        $emailType = isset($emailRow['type']) ? (string)$emailRow['type'] : 'Mail';

        $Email = new CakeEmail();
        $Email->transport($emailType);
        if($emailType === 'Smtp')
        {
            $Email->config(array(
                'host' => isset($emailRow['host']) ? $emailRow['host'] : '',
                'port' => isset($emailRow['port']) ? $emailRow['port'] : '',
                'username' => isset($emailRow['username']) ? $emailRow['username'] : '',
                'password' => isset($emailRow['password']) ? $emailRow['password'] : '',
                'timeout' => 90
            ));
        }
        $Email->from(array($this->siteEmail => $this->siteName));
        $Email->to($toEmail);
        $Email->template('default');
        $Email->emailFormat('html');
        $Email->subject($subject);
        $Email->send($message);
        return true;
    }

    protected function _normalizeScopedUserId($projectId, $ugroupId, $requestedUserId)
    {
        $requestedUserId = (int)$requestedUserId;
        if($requestedUserId <= 0)
        {
            return 0;
        }
        $this->loadModel('User');
        $conditions = array($this->User->assignableScopeConditions((int)$projectId, 'User'));
        $conditions[] = array('User.id' => $requestedUserId);
        if((int)$ugroupId > 0)
        {
            $conditions[] = array('User.ugroup_id' => (int)$ugroupId);
        }
        $row = (array)$this->User->find('first', array(
            'fields' => array('User.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        return !empty($row['User']['id']) ? (int)$row['User']['id'] : 0;
    }

    protected function _normalizeSettingsData($provider, $rawData = array())
    {
        $provider = strtolower(trim((string)$provider));
        $defaults = $this->CrmIntegration->defaultProviderRow($provider);
        $rawData = (array)$rawData;
        $projectId = $this->_normalizePositiveId(isset($rawData['default_project_id']) ? $rawData['default_project_id'] : (isset($rawData['project_id']) ? $rawData['project_id'] : 0));
        $ugroupId = $this->_normalizePositiveId(isset($rawData['default_ugroup_id']) ? $rawData['default_ugroup_id'] : (isset($rawData['ugroup_id']) ? $rawData['ugroup_id'] : 0));
        $userId = $this->_normalizeScopedUserId($projectId, $ugroupId, isset($rawData['default_user_id']) ? $rawData['default_user_id'] : (isset($rawData['user_id']) ? $rawData['user_id'] : 0));
        return array(
            'provider' => $provider,
            'title' => (string)$defaults['CrmIntegration']['title'],
            'enabled' => $this->_normalizeEnabledFlag(isset($rawData['enabled']) ? $rawData['enabled'] : 0),
            'verify_token' => trim((string)(isset($rawData['verify_token']) ? $rawData['verify_token'] : '')),
            'access_token' => trim((string)(isset($rawData['access_token']) ? $rawData['access_token'] : '')),
            'app_id' => trim((string)(isset($rawData['app_id']) ? $rawData['app_id'] : '')),
            'app_secret' => trim((string)(isset($rawData['app_secret']) ? $rawData['app_secret'] : '')),
            'page_id' => trim((string)(isset($rawData['page_id']) ? $rawData['page_id'] : '')),
            'business_account_id' => trim((string)(isset($rawData['business_account_id']) ? $rawData['business_account_id'] : '')),
            'phone_number_id' => trim((string)(isset($rawData['phone_number_id']) ? $rawData['phone_number_id'] : '')),
            'default_project_id' => $projectId,
            'default_ugroup_id' => $ugroupId,
            'default_user_id' => $userId,
            'default_status_id' => $this->_normalizePositiveId(isset($rawData['default_status_id']) ? $rawData['default_status_id'] : (isset($rawData['status_id']) ? $rawData['status_id'] : 0)),
            'default_lead_source' => trim((string)(isset($rawData['default_lead_source']) ? $rawData['default_lead_source'] : (isset($rawData['lead_source']) ? $rawData['lead_source'] : $defaults['CrmIntegration']['default_lead_source']))),
            'auto_create_contact' => $this->_normalizeEnabledFlag(isset($rawData['auto_create_contact']) ? $rawData['auto_create_contact'] : 0),
            'settings_json' => ''
        );
    }

    protected function _routeRowById($id)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            return array();
        }
        $this->loadModel('CrmIntegrationRoute');
        return (array)$this->CrmIntegrationRoute->find('first', array(
            'conditions' => array('CrmIntegrationRoute.id' => $id),
            'recursive' => 0
        ));
    }

    protected function _defaultRouteFormData()
    {
        return array(
            'id' => 0,
            'provider' => 'facebook_leads',
            'match_type' => 'form_id',
            'external_id' => '',
            'title' => '',
            'project_id' => 0,
            'ugroup_id' => 0,
            'user_id' => 0,
            'status_id' => 0,
            'lead_source' => '',
            'auto_create_contact' => 0,
            'is_active' => 1
        );
    }

    protected function _normalizeRouteData($rawData = array())
    {
        $rawData = (array)$rawData;
        $routeData = $this->_defaultRouteFormData();
        $routeData['id'] = $this->_normalizePositiveId(isset($rawData['id']) ? $rawData['id'] : 0);
        $routeData['provider'] = strtolower(trim((string)(isset($rawData['provider']) ? $rawData['provider'] : $routeData['provider'])));
        $allowedProviders = array_keys($this->_providerOptions());
        if(!in_array($routeData['provider'], $allowedProviders, true))
        {
            $routeData['provider'] = 'facebook_leads';
        }
        $allowedRouteTypes = array_keys($this->_routeTypeOptions($routeData['provider']));
        $routeData['match_type'] = strtolower(trim((string)(isset($rawData['match_type']) ? $rawData['match_type'] : $routeData['match_type'])));
        if(!in_array($routeData['match_type'], $allowedRouteTypes, true))
        {
            $routeData['match_type'] = reset($allowedRouteTypes);
        }
        $routeData['external_id'] = trim((string)(isset($rawData['external_id']) ? $rawData['external_id'] : ''));
        $routeData['title'] = trim((string)(isset($rawData['title']) ? $rawData['title'] : ''));
        $routeData['project_id'] = $this->_normalizePositiveId(isset($rawData['project_id']) ? $rawData['project_id'] : 0);
        $routeData['ugroup_id'] = $this->_normalizePositiveId(isset($rawData['ugroup_id']) ? $rawData['ugroup_id'] : 0);
        $routeData['user_id'] = $this->_normalizeScopedUserId($routeData['project_id'], $routeData['ugroup_id'], isset($rawData['user_id']) ? $rawData['user_id'] : 0);
        $routeData['status_id'] = $this->_normalizePositiveId(isset($rawData['status_id']) ? $rawData['status_id'] : 0);
        $routeData['lead_source'] = trim((string)(isset($rawData['lead_source']) ? $rawData['lead_source'] : ''));
        $routeData['auto_create_contact'] = $this->_normalizeEnabledFlag(isset($rawData['auto_create_contact']) ? $rawData['auto_create_contact'] : 0);
        $routeData['is_active'] = $this->_normalizeEnabledFlag(isset($rawData['is_active']) ? $rawData['is_active'] : 0);
        return $routeData;
    }

    protected function _validateRouteData($routeData = array())
    {
        $routeData = (array)$routeData;
        $errors = array();
        if(trim((string)$routeData['external_id']) === '')
        {
            $errors[] = __('Please enter external source ID.');
        }
        if((int)$routeData['project_id'] <= 0)
        {
            $errors[] = __('Please select agency.');
        }
        if((int)$routeData['user_id'] <= 0)
        {
            $errors[] = __('Please select user.');
        }
        if(!empty($routeData['id']) || trim((string)$routeData['external_id']) !== '')
        {
            $this->loadModel('CrmIntegrationRoute');
            $duplicateConditions = array(
                'CrmIntegrationRoute.provider' => (string)$routeData['provider'],
                'CrmIntegrationRoute.match_type' => (string)$routeData['match_type'],
                'CrmIntegrationRoute.external_id' => (string)$routeData['external_id']
            );
            if((int)$routeData['id'] > 0)
            {
                $duplicateConditions['CrmIntegrationRoute.id !='] = (int)$routeData['id'];
            }
            $duplicateCount = (int)$this->CrmIntegrationRoute->find('count', array(
                'conditions' => $duplicateConditions,
                'recursive' => -1
            ));
            if($duplicateCount > 0)
            {
                $errors[] = __('This provider/source ID mapping already exists.');
            }
        }
        return $errors;
    }

    protected function _superAdminGroupIds()
    {
        $this->loadModel('Ugroup');
        $rows = (array)$this->Ugroup->find('all', array(
            'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
            'recursive' => -1
        ));
        $groupIds = array();
        foreach($rows as $row)
        {
            $groupId = !empty($row['Ugroup']['id']) ? (int)$row['Ugroup']['id'] : 0;
            $type = !empty($row['Ugroup']['type']) ? strtoupper(trim((string)$row['Ugroup']['type'])) : '';
            $name = !empty($row['Ugroup']['name']) ? strtoupper(trim((string)$row['Ugroup']['name'])) : '';
            if($groupId > 0 && ($groupId === 1 || $type === 'ADR' || preg_match('/\bSUPER\b.*\bADMIN\b/', $name)))
            {
                $groupIds[$groupId] = $groupId;
            }
        }
        if(empty($groupIds))
        {
            $groupIds[1] = 1;
        }
        return array_values($groupIds);
    }

    protected function _agencyAdminGroupIds()
    {
        $this->loadModel('Ugroup');
        $rows = (array)$this->Ugroup->find('all', array(
            'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
            'recursive' => -1
        ));
        $groupIds = array();
        foreach($rows as $row)
        {
            $groupId = !empty($row['Ugroup']['id']) ? (int)$row['Ugroup']['id'] : 0;
            $type = !empty($row['Ugroup']['type']) ? strtoupper(trim((string)$row['Ugroup']['type'])) : '';
            $name = !empty($row['Ugroup']['name']) ? strtoupper(trim((string)$row['Ugroup']['name'])) : '';
            if($groupId > 0 && ($type === 'AGY' || $name === 'AGENCY'))
            {
                $groupIds[$groupId] = $groupId;
            }
        }
        return array_values($groupIds);
    }

    protected function _isAgencyAdminUser()
    {
        return (strtoupper(trim((string)$this->userType)) === 'AGY');
    }

    protected function _canUseCrmBulkImport()
    {
        return ($this->_isSuperAdminSession() || $this->_isAgencyAdminUser());
    }

    protected function _ensureLeadImportSchema()
    {
        $this->loadModel('Lead');
        $columns = array(
            'lead_name' => "varchar(255) DEFAULT NULL",
            'lead_mobile' => "varchar(60) DEFAULT NULL",
            'lead_email' => "varchar(255) DEFAULT NULL",
            'lead_address' => "text NULL",
            'lead_city' => "varchar(150) DEFAULT NULL",
            'lead_budget' => "decimal(15,2) DEFAULT NULL",
            'lead_language' => "varchar(80) DEFAULT NULL",
            'lead_source' => "varchar(150) DEFAULT NULL",
            'source_form_id' => "varchar(120) DEFAULT NULL",
            'source_ad_id' => "varchar(120) DEFAULT NULL",
            'utm_source' => "varchar(191) DEFAULT NULL",
            'utm_campaign' => "varchar(191) DEFAULT NULL",
            'utm_medium' => "varchar(191) DEFAULT NULL",
            'utm_term' => "varchar(191) DEFAULT NULL",
            'utm_content' => "varchar(191) DEFAULT NULL",
            'last_contact_date' => "date DEFAULT NULL",
            'lead_temperature' => "varchar(12) DEFAULT 'warm'",
            'kanban_position' => "int(11) DEFAULT 0"
        );
        foreach($columns as $columnName => $columnSql)
        {
            try
            {
                $exists = $this->Lead->query("SHOW COLUMNS FROM `leads` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $this->Lead->query("ALTER TABLE `leads` ADD `{$columnName}` {$columnSql}");
                }
            }
            catch(Exception $e)
            {
            }
        }

        static $clientNullableEnsured = null;
        if($clientNullableEnsured !== null)
        {
            return $clientNullableEnsured;
        }
        $clientNullableEnsured = false;
        try
        {
            $clientColumnRows = (array)$this->Lead->query("SHOW COLUMNS FROM `leads` LIKE 'client_id'");
            if(empty($clientColumnRows))
            {
                return false;
            }
            $columnMeta = reset($clientColumnRows);
            $columnInfo = array();
            if(isset($columnMeta['COLUMNS']))
            {
                $columnInfo = (array)$columnMeta['COLUMNS'];
            }
            elseif(isset($columnMeta[0]))
            {
                $columnInfo = (array)$columnMeta[0];
            }
            $isNullable = !empty($columnInfo['Null']) && strtoupper((string)$columnInfo['Null']) === 'YES';
            if($isNullable)
            {
                $clientNullableEnsured = true;
                return true;
            }

            $constraintName = '';
            $constraintRows = (array)$this->Lead->query(
                "SELECT `CONSTRAINT_NAME` FROM `information_schema`.`KEY_COLUMN_USAGE` ".
                "WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'leads' AND `COLUMN_NAME` = 'client_id' ".
                "AND `REFERENCED_TABLE_NAME` = 'clients' LIMIT 1"
            );
            if(!empty($constraintRows[0]['KEY_COLUMN_USAGE']['CONSTRAINT_NAME']))
            {
                $constraintName = (string)$constraintRows[0]['KEY_COLUMN_USAGE']['CONSTRAINT_NAME'];
            }
            elseif(!empty($constraintRows[0][0]['CONSTRAINT_NAME']))
            {
                $constraintName = (string)$constraintRows[0][0]['CONSTRAINT_NAME'];
            }
            if($constraintName !== '')
            {
                $this->Lead->query("ALTER TABLE `leads` DROP FOREIGN KEY `".addslashes($constraintName)."`");
            }
            $this->Lead->query("ALTER TABLE `leads` MODIFY `client_id` int(11) DEFAULT NULL");
            $this->Lead->query(
                "ALTER TABLE `leads` ADD CONSTRAINT `leads_ibfk_5` FOREIGN KEY (`client_id`) ".
                "REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE"
            );
            $clientNullableEnsured = true;
        }
        catch(Exception $e)
        {
            $clientNullableEnsured = false;
        }
        return $clientNullableEnsured;
    }

    protected function _parseCsvRows($filePath)
    {
        $rows = array();
        $handle = fopen($filePath, 'r');
        if($handle === false)
        {
            return $rows;
        }
        while(($data = fgetcsv($handle)) !== false)
        {
            if(!is_array($data))
            {
                continue;
            }
            $rows[] = $data;
        }
        fclose($handle);
        return $rows;
    }

    protected function _xlsxColumnToIndex($letters)
    {
        $letters = strtoupper(trim((string)$letters));
        $index = 0;
        $len = strlen($letters);
        for($i=0;$i<$len;$i++)
        {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }
        return max(0, $index - 1);
    }

    protected function _parseXlsxRows($filePath)
    {
        $rows = array();
        if(!class_exists('ZipArchive'))
        {
            return $rows;
        }
        $zip = new ZipArchive();
        if($zip->open($filePath) !== true)
        {
            return $rows;
        }

        $sharedStrings = array();
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if($sharedXml !== false)
        {
            $domShared = new DOMDocument();
            if(@$domShared->loadXML($sharedXml))
            {
                $xpathShared = new DOMXPath($domShared);
                $xpathShared->registerNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $siNodes = $xpathShared->query('//a:si');
                foreach($siNodes as $siNode)
                {
                    $parts = $xpathShared->query('.//a:t', $siNode);
                    $text = '';
                    foreach($parts as $partNode)
                    {
                        $text .= (string)$partNode->nodeValue;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if($sheetXml === false)
        {
            $zip->close();
            return $rows;
        }
        $zip->close();

        $domSheet = new DOMDocument();
        if(!@$domSheet->loadXML($sheetXml))
        {
            return $rows;
        }
        $xpathSheet = new DOMXPath($domSheet);
        $xpathSheet->registerNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $xpathSheet->query('//a:sheetData/a:row');
        foreach($rowNodes as $rowNode)
        {
            $row = array();
            $currentIndex = 0;
            $cellNodes = $xpathSheet->query('a:c', $rowNode);
            foreach($cellNodes as $cellNode)
            {
                $ref = (string)$cellNode->getAttribute('r');
                $letters = preg_replace('/[^A-Z]/i', '', $ref);
                $targetIndex = $this->_xlsxColumnToIndex($letters);
                while($currentIndex < $targetIndex)
                {
                    $row[] = '';
                    $currentIndex++;
                }

                $cellType = (string)$cellNode->getAttribute('t');
                $cellValue = '';
                if($cellType === 's')
                {
                    $valueNode = $xpathSheet->query('a:v', $cellNode)->item(0);
                    $sharedIndex = $valueNode ? (int)$valueNode->nodeValue : -1;
                    $cellValue = isset($sharedStrings[$sharedIndex]) ? $sharedStrings[$sharedIndex] : '';
                }
                elseif($cellType === 'inlineStr')
                {
                    $textNode = $xpathSheet->query('a:is/a:t', $cellNode)->item(0);
                    $cellValue = $textNode ? (string)$textNode->nodeValue : '';
                }
                else
                {
                    $valueNode = $xpathSheet->query('a:v', $cellNode)->item(0);
                    $cellValue = $valueNode ? (string)$valueNode->nodeValue : '';
                }

                $row[] = $cellValue;
                $currentIndex++;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    protected function _parseLeadImportRows($filePath, $originalName)
    {
        $extension = strtolower(pathinfo((string)$originalName, PATHINFO_EXTENSION));
        if($extension === 'xlsx')
        {
            return $this->_parseXlsxRows($filePath);
        }
        return $this->_parseCsvRows($filePath);
    }

    protected function _mapLeadImportHeaders($headers)
    {
        $aliasMap = array(
            'lead_name' => 'lead_name',
            'name' => 'lead_name',
            'client_name' => 'lead_name',
            'contact_name' => 'lead_name',
            'lead_mobile' => 'lead_mobile',
            'mobile' => 'lead_mobile',
            'phone' => 'lead_mobile',
            'lead_phone' => 'lead_mobile',
            'lead_email' => 'lead_email',
            'email' => 'lead_email',
            'lead_address' => 'lead_address',
            'address' => 'lead_address',
            'lead_city' => 'lead_city',
            'city' => 'lead_city',
            'lead_budget' => 'lead_budget',
            'budget' => 'lead_budget',
            'lead_language' => 'lead_language',
            'language' => 'lead_language',
            'lead_source' => 'lead_source',
            'source' => 'lead_source',
            'source_form_id' => 'source_form_id',
            'form_id' => 'source_form_id',
            'source_ad_id' => 'source_ad_id',
            'ad_id' => 'source_ad_id',
            'utm_source' => 'utm_source',
            'utm_campaign' => 'utm_campaign',
            'utm_medium' => 'utm_medium',
            'utm_term' => 'utm_term',
            'utm_content' => 'utm_content',
            'last_contact_date' => 'last_contact_date',
            'last_contact' => 'last_contact_date',
            'contact_date' => 'last_contact_date',
            'lead_temperature' => 'lead_temperature',
            'temperature' => 'lead_temperature',
            'priority' => 'lead_temperature',
            'badge' => 'lead_temperature',
            'status' => 'status',
            'status_name' => 'status',
            'status_id' => 'status',
            'follow_up' => 'follow_up',
            'followup' => 'follow_up',
            'next_follow_up' => 'follow_up',
            'remarks' => 'remarks',
            'remark' => 'remarks',
            'notes' => 'remarks',
            'property' => 'property',
            'property_name' => 'property',
            'property_id' => 'property',
            'client_id' => 'client_id'
        );

        $map = array();
        foreach((array)$headers as $index => $rawHeader)
        {
            $normalized = strtolower(trim((string)$rawHeader));
            $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
            $normalized = trim($normalized, '_');
            if($normalized === '')
            {
                continue;
            }
            if(isset($aliasMap[$normalized]))
            {
                $map[$index] = $aliasMap[$normalized];
            }
        }
        return $map;
    }

    protected function _safeLeadBudgetValue($value)
    {
        if($value === null)
        {
            return null;
        }
        $text = trim((string)$value);
        if($text === '')
        {
            return null;
        }
        $text = preg_replace('/[^0-9\.\-]/', '', $text);
        if($text === '' || !is_numeric($text))
        {
            return null;
        }
        return round((float)$text, 2);
    }

    protected function _defaultLeadStatusId()
    {
        $this->loadModel('Status');
        $row = $this->Status->find('first', array(
            'fields' => array('Status.id'),
            'order' => array('Status.id' => 'asc'),
            'recursive' => -1
        ));
        return !empty($row['Status']['id']) ? (int)$row['Status']['id'] : 1;
    }

    protected function _resolveLeadStatusId($statusValue)
    {
        $statusValue = trim((string)$statusValue);
        if($statusValue === '')
        {
            return $this->_defaultLeadStatusId();
        }
        if(ctype_digit($statusValue) && (int)$statusValue > 0)
        {
            return (int)$statusValue;
        }
        $this->loadModel('Status');
        $row = $this->Status->find('first', array(
            'fields' => array('Status.id'),
            'conditions' => array('LOWER(Status.name)' => strtolower($statusValue)),
            'recursive' => -1
        ));
        if(!empty($row['Status']['id']))
        {
            return (int)$row['Status']['id'];
        }
        return $this->_defaultLeadStatusId();
    }

    protected function _normalizeLeadTemperature($temperatureValue)
    {
        $temperatureValue = strtolower(trim((string)$temperatureValue));
        if(in_array($temperatureValue, array('hot', 'warm', 'cold'), true))
        {
            return $temperatureValue;
        }
        return 'warm';
    }

    protected function _resolveLeadPropertyId($projectId, $propertyValue)
    {
        $propertyValue = trim((string)$propertyValue);
        if($propertyValue === '')
        {
            return 0;
        }
        if(ctype_digit($propertyValue) && (int)$propertyValue > 0)
        {
            return (int)$propertyValue;
        }
        $this->loadModel('Property');
        $conditions = array('Property.name LIKE' => $propertyValue.'%');
        if((int)$projectId > 0)
        {
            $conditions['Property.project_id'] = (int)$projectId;
        }
        $row = $this->Property->find('first', array(
            'fields' => array('Property.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        if(!empty($row['Property']['id']))
        {
            return (int)$row['Property']['id'];
        }
        return 0;
    }

    protected function _sanitizeMobile($mobileValue)
    {
        $mobileValue = trim((string)$mobileValue);
        $mobileValue = preg_replace('/\s+/', '', $mobileValue);
        return $mobileValue;
    }

    protected function _normalizeEmail($emailValue)
    {
        return strtolower(trim((string)$emailValue));
    }

    protected function _findContactByLeadData($projectId, $mobileNo, $emailAddress, $providedClientId = 0)
    {
        $this->loadModel('Admin.Contact');
        $providedClientId = (int)$providedClientId;
        if($providedClientId > 0)
        {
            $conditions = array('Contact.id' => $providedClientId);
            if((int)$projectId > 0)
            {
                $conditions['Contact.project_id'] = (int)$projectId;
            }
            $row = $this->Contact->find('first', array(
                'fields' => array('Contact.id'),
                'conditions' => $conditions,
                'recursive' => -1
            ));
            if(!empty($row['Contact']['id']))
            {
                return (int)$row['Contact']['id'];
            }
        }

        $orConditions = array();
        if(strlen(trim($mobileNo)) > 0)
        {
            $orConditions[] = array('Contact.mobile' => $mobileNo);
            $orConditions[] = array('Contact.phone' => $mobileNo);
        }
        if(strlen(trim($emailAddress)) > 0)
        {
            $orConditions[] = array('LOWER(Contact.email)' => strtolower($emailAddress));
        }
        if(empty($orConditions))
        {
            return 0;
        }

        $conditions = array('OR' => $orConditions);
        if((int)$projectId > 0)
        {
            $conditions['Contact.project_id'] = (int)$projectId;
        }
        $row = $this->Contact->find('first', array(
            'fields' => array('Contact.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        return !empty($row['Contact']['id']) ? (int)$row['Contact']['id'] : 0;
    }

    protected function _createContactFromLeadData($projectId, $userId, $leadName, $mobileNo, $emailAddress, $leadAddress = '')
    {
        $this->loadModel('Admin.Contact');
        $this->loadModel('Category');

        $category = $this->Category->find('first', array(
            'fields' => array('Category.id'),
            'conditions' => array('Category.type' => NULL),
            'order' => array('Category.id' => 'asc'),
            'recursive' => -1
        ));
        $categoryId = !empty($category['Category']['id']) ? (int)$category['Category']['id'] : 1;
        if($categoryId <= 0)
        {
            $categoryId = 1;
        }

        $plainPassword = (string)rand(100000, 999999);
        $data = array(
            'Contact' => array(
                'category_id' => $categoryId,
                'user_id' => (int)$userId,
                'project_id' => (int)$projectId,
                'name' => trim((string)$leadName),
                'mobile' => trim((string)$mobileNo),
                'phone' => trim((string)$mobileNo),
                'email' => trim((string)$emailAddress),
                'address' => trim((string)$leadAddress),
                'password' => $this->_hashPassword($plainPassword),
                'status' => 'Active',
                'reg_status' => 'Live'
            )
        );
        $this->Contact->create();
        if($this->Contact->save($data, array('validate' => false)))
        {
            return (int)$this->Contact->id;
        }
        return 0;
    }

    protected function _safeFollowUpDateTime($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return null;
        }
        $knownFormats = array(
            'Y-m-d H:i:s',
            'Y-m-d h:i:s A',
            'Y-m-d h:i:s a',
            'Y-m-d H:i',
            'Y/m/d H:i',
            'd-m-Y H:i',
            'd/m/Y H:i',
            'd.m.Y H:i',
            'm/d/Y H:i',
            'm-d-Y H:i',
            'd-m-Y h:i A',
            'd-m-Y h:i a',
            'd/m/Y h:i A',
            'd/m/Y h:i a',
            'm/d/Y h:i A',
            'm/d/Y h:i a',
            'm-d-Y h:i A',
            'm-d-Y h:i a',
            'Y-m-d h:i A',
            'Y-m-d h:i a',
            'Y/m/d h:i A',
            'Y/m/d h:i a'
        );
        foreach($knownFormats as $knownFormat)
        {
            $dateObj = DateTime::createFromFormat($knownFormat, $value);
            if($dateObj instanceof DateTime)
            {
                $errors = DateTime::getLastErrors();
                if((int)$errors['warning_count'] === 0 && (int)$errors['error_count'] === 0)
                {
                    return $dateObj->format('Y-m-d H:i:s');
                }
            }
        }
        $timestamp = strtotime($value);
        if($timestamp === false)
        {
            return null;
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    protected function _leadNormalizeDateInput($rawValue)
    {
        $rawValue = trim((string)$rawValue);
        if($rawValue === '')
        {
            return '';
        }
        $knownFormats = array(
            'Y-m-d',
            'Y/m/d',
            'd-m-Y',
            'd/m/Y',
            'd.m.Y',
            'm/d/Y',
            'm-d-Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'm-d-Y H:i:s',
            'm-d-Y H:i',
            'd-m-Y h:i A',
            'd-m-Y h:i a',
            'd/m/Y h:i A',
            'd/m/Y h:i a',
            'm/d/Y h:i A',
            'm/d/Y h:i a',
            'm-d-Y h:i A',
            'm-d-Y h:i a',
            'Y-m-d h:i A',
            'Y-m-d h:i a',
            'Y/m/d h:i A',
            'Y/m/d h:i a'
        );
        foreach($knownFormats as $knownFormat)
        {
            $dateObj = DateTime::createFromFormat($knownFormat, $rawValue);
            if($dateObj instanceof DateTime)
            {
                $errors = DateTime::getLastErrors();
                if((int)$errors['warning_count'] === 0 && (int)$errors['error_count'] === 0)
                {
                    return $dateObj->format('Y-m-d');
                }
            }
        }
        $timestamp = strtotime($rawValue);
        if($timestamp === false)
        {
            return '';
        }
        return date('Y-m-d', $timestamp);
    }

    protected function _buildImportedLeadPayload($formData, $projectId, $userId, $defaultStatusId = null)
    {
        if($defaultStatusId === null)
        {
            $defaultStatusId = $this->_defaultLeadStatusId();
        }
        $leadName = trim((string)$formData['lead_name']);
        $leadMobile = $this->_sanitizeMobile(isset($formData['lead_mobile']) ? $formData['lead_mobile'] : '');
        $leadEmail = $this->_normalizeEmail(isset($formData['lead_email']) ? $formData['lead_email'] : '');
        $leadAddress = trim((string)(isset($formData['lead_address']) ? $formData['lead_address'] : ''));
        $leadCity = trim((string)(isset($formData['lead_city']) ? $formData['lead_city'] : (isset($formData['city']) ? $formData['city'] : '')));
        $leadBudget = $this->_safeLeadBudgetValue(isset($formData['lead_budget']) ? $formData['lead_budget'] : (isset($formData['budget']) ? $formData['budget'] : null));
        $leadLanguage = trim((string)(isset($formData['lead_language']) ? $formData['lead_language'] : (isset($formData['language']) ? $formData['language'] : '')));
        $leadSource = trim((string)(isset($formData['lead_source']) ? $formData['lead_source'] : ''));
        $sourceFormId = trim((string)(isset($formData['source_form_id']) ? $formData['source_form_id'] : (isset($formData['form_id']) ? $formData['form_id'] : '')));
        $sourceAdId = trim((string)(isset($formData['source_ad_id']) ? $formData['source_ad_id'] : (isset($formData['ad_id']) ? $formData['ad_id'] : '')));
        $utmSource = trim((string)(isset($formData['utm_source']) ? $formData['utm_source'] : ''));
        $utmCampaign = trim((string)(isset($formData['utm_campaign']) ? $formData['utm_campaign'] : ''));
        $utmMedium = trim((string)(isset($formData['utm_medium']) ? $formData['utm_medium'] : ''));
        $utmTerm = trim((string)(isset($formData['utm_term']) ? $formData['utm_term'] : ''));
        $utmContent = trim((string)(isset($formData['utm_content']) ? $formData['utm_content'] : ''));
        $statusId = $this->_resolveLeadStatusId(isset($formData['status_id']) ? $formData['status_id'] : $defaultStatusId);
        $lastContactDate = $this->_leadNormalizeDateInput(isset($formData['last_contact_date']) ? $formData['last_contact_date'] : '');
        $followUp = $this->_safeFollowUpDateTime(isset($formData['follow_up']) ? $formData['follow_up'] : '');
        $leadTemperature = $this->_normalizeLeadTemperature(isset($formData['lead_temperature']) ? $formData['lead_temperature'] : 'warm');
        $propertyId = $this->_resolveLeadPropertyId($projectId, isset($formData['property_id']) ? $formData['property_id'] : '');
        $remarks = trim((string)(isset($formData['remarks']) ? $formData['remarks'] : ''));
        $createContactFlagProvided = array_key_exists('create_contact', (array)$formData);
        $autoCreateContact = !empty($formData['create_contact']) ? 1 : 0;
        $allowContactLinking = (!$createContactFlagProvided || $autoCreateContact === 1);
        $clientId = 0;
        if($allowContactLinking)
        {
            $clientId = $this->_findContactByLeadData($projectId, $leadMobile, $leadEmail, isset($formData['client_id']) ? $formData['client_id'] : 0);
            if($clientId <= 0 && $leadName !== '')
            {
                $createdClientId = $this->_createContactFromLeadData($projectId, $userId, $leadName, $leadMobile, $leadEmail, $leadAddress);
                if($createdClientId > 0)
                {
                    $clientId = $createdClientId;
                }
            }
        }

        return array(
            'project_id' => (int)$projectId,
            'user_id' => (int)$userId,
            'client_id' => ((int)$clientId > 0 ? (int)$clientId : null),
            'property_id' => ((int)$propertyId > 0 ? (int)$propertyId : null),
            'status_id' => (int)$statusId,
            'last_contact_date' => ($lastContactDate !== '' ? $lastContactDate : null),
            'follow_up' => $followUp,
            'remarks' => $remarks,
            'lead_name' => $leadName,
            'lead_mobile' => $leadMobile,
            'lead_email' => $leadEmail,
            'lead_address' => $leadAddress,
            'lead_city' => ($leadCity !== '' ? $leadCity : null),
            'lead_budget' => $leadBudget,
            'lead_language' => ($leadLanguage !== '' ? $leadLanguage : null),
            'lead_source' => $leadSource,
            'source_form_id' => ($sourceFormId !== '' ? $sourceFormId : null),
            'source_ad_id' => ($sourceAdId !== '' ? $sourceAdId : null),
            'utm_source' => ($utmSource !== '' ? $utmSource : null),
            'utm_campaign' => ($utmCampaign !== '' ? $utmCampaign : null),
            'utm_medium' => ($utmMedium !== '' ? $utmMedium : null),
            'utm_term' => ($utmTerm !== '' ? $utmTerm : null),
            'utm_content' => ($utmContent !== '' ? $utmContent : null),
            'lead_temperature' => $leadTemperature
        );
    }

    protected function _applyCrmAutomationToLeadPayload($payload = array(), $scope = 'import', $excludeLeadId = 0)
    {
        $payload = (array)$payload;
        try
        {
            $CrmAutomationSetting = ClassRegistry::init('CrmAutomationSetting');
            if($CrmAutomationSetting)
            {
                $CrmAutomationSetting->ensureSchema();
                return (array)$CrmAutomationSetting->applyToLeadPayload($payload, array(
                    'scope' => $scope,
                    'exclude_lead_id' => (int)$excludeLeadId
                ));
            }
        }
        catch(Exception $e)
        {
        }
        return array(
            'success' => true,
            'payload' => $payload,
            'duplicate_lead_id' => 0
        );
    }

    protected function _ensureCrmIntegrationMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'leads',
                    'Page.parent_id' => 0
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootId = !empty($root['Page']['id']) ? (int)$root['Page']['id'] : 0;
            if($rootId <= 0)
            {
                $Page->create();
                $Page->save(array('Page' => array(
                    'model_name' => 'Leads',
                    'page_name' => 'Leads CRM',
                    'controller_name' => 'Leads',
                    'action_name' => 'dashboard',
                    'icon' => 'fa fa-bullhorn',
                    'parent_id' => 0,
                    'ordering' => 6,
                    'published' => 'Yes',
                    'sel_name' => ''
                )));
                $rootId = (int)$Page->id;
            }
            if($rootId <= 0)
            {
                return;
            }

            $pages = array(
                array('page_name' => 'CRM Bulk Import', 'controller_name' => 'Crmintegrations', 'action_name' => 'bulkimport', 'icon' => 'fa fa-upload', 'ordering' => 11),
                array('page_name' => 'CRM Sync Logs', 'controller_name' => 'Crmintegrations', 'action_name' => 'logs', 'icon' => 'fa fa-history', 'ordering' => 12),
                array('page_name' => 'CRM Routing', 'controller_name' => 'Crmintegrations', 'action_name' => 'routes', 'icon' => 'fa fa-random', 'ordering' => 13),
                array('page_name' => 'CRM Settings', 'controller_name' => 'Crmintegrations', 'action_name' => 'index', 'icon' => 'fa fa-plug', 'ordering' => 16)
            );
            $pageIds = array();
            $bulkImportPageId = 0;
            foreach($pages as $pageRow)
            {
                $existing = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'Page.parent_id' => $rootId,
                        'LOWER(Page.controller_name)' => strtolower($pageRow['controller_name']),
                        'LOWER(Page.action_name)' => strtolower($pageRow['action_name'])
                    ),
                    'recursive' => -1
                ));
                $saveData = array(
                    'model_name' => 'Leads',
                    'page_name' => $pageRow['page_name'],
                    'controller_name' => $pageRow['controller_name'],
                    'action_name' => $pageRow['action_name'],
                    'icon' => $pageRow['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $pageRow['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if(!empty($existing['Page']['id']))
                {
                    $saveData['id'] = (int)$existing['Page']['id'];
                }
                $Page->create();
                $Page->save(array('Page' => $saveData), array('validate' => false));
                $pageId = !empty($saveData['id']) ? (int)$saveData['id'] : (int)$Page->id;
                if($pageId > 0)
                {
                    $pageIds[$pageId] = $pageId;
                    if(strtolower((string)$pageRow['action_name']) === 'bulkimport')
                    {
                        $bulkImportPageId = $pageId;
                    }
                }
            }

            $superGroupIds = $this->_superAdminGroupIds();
            $agencyGroupIds = $this->_agencyAdminGroupIds();
            foreach($pageIds as $pageId)
            {
                $grantGroupIds = $superGroupIds;
                if($bulkImportPageId > 0 && (int)$pageId === (int)$bulkImportPageId)
                {
                    $grantGroupIds = array_values(array_unique(array_merge($grantGroupIds, $agencyGroupIds)));
                }
                foreach($grantGroupIds as $groupId)
                {
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$pageId,
                            'PageRight.ugroup_id' => (int)$groupId
                        ),
                        'recursive' => -1
                    ));
                    $saveRight = array(
                        'page_id' => (int)$pageId,
                        'ugroup_id' => (int)$groupId,
                        'view_right' => 1,
                        'save_right' => 1,
                        'update_right' => 1,
                        'search_right' => 1
                    );
                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $saveRight['id'] = (int)$existingRight['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $saveRight), array('validate' => false));
                }
            }
        }
        catch(Exception $e)
        {
        }
    }

    public function index()
    {
        $providerOptions = $this->_settingsTabOptions();
        $integrationProviderOptions = $this->_providerOptions();
        $activeTab = isset($this->request->query['tab']) ? strtolower(trim((string)$this->request->query['tab'])) : 'sms_gateway';
        if(!isset($providerOptions[$activeTab]))
        {
            $activeTab = 'sms_gateway';
        }

        $currentUserId = $this->_currentAdminUserId();
        $isSuperAdmin = $this->_isSuperAdminSession();
        $this->loadModel('Smssetting');
        $this->loadModel('Emailsetting');
        $globalSmsSettings = (array)$this->Smssetting->findById(1);
        $globalEmailSettings = (array)$this->Emailsetting->findById(1);
        $smsUserSettings = $this->CrmIntegration->findUserSmsRow($currentUserId);
        $emailUserSettings = $this->CrmIntegration->findUserEmailRow($currentUserId);
        $smsTemplateOptions = $this->_leadTemplateBundle('sms', $currentUserId);
        $emailTemplateOptions = $this->_leadTemplateBundle('email', $currentUserId);
        $savedSmsTemplateId = (int)$this->CrmIntegration->storedSmsTemplateIdForUser($currentUserId);
        $savedEmailTemplateId = (int)$this->CrmIntegration->storedEmailTemplateIdForUser($currentUserId);
        $googleGlobalSettings = $this->CrmGoogleCalendar->findGlobalRow();
        $googleUserSettings = $this->CrmGoogleCalendar->findUserRow($currentUserId);
        $googleEffectiveSettings = $this->CrmGoogleCalendar->effectiveSettingsForUser($currentUserId);

        if($this->request->is('post'))
        {
            $provider = strtolower(trim((string)$this->_postedNestedValue((array)$this->request->data, 'provider', '')));
            $googleSettingsProviders = array('google_calendar_global', 'google_calendar_user');
            if(!isset($providerOptions[$provider]) && !in_array($provider, $googleSettingsProviders, true))
            {
                $this->Session->setFlash(__('Invalid provider selection.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            if(isset($integrationProviderOptions[$provider]))
            {
                $saveData = $this->_normalizeSettingsData($provider, (array)$this->request->data);
                if($this->CrmIntegration->saveProviderRow($provider, $saveData))
                {
                    $this->Session->setFlash(__('CRM integration settings have been saved.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'index', '?' => array('tab' => $provider)));
                }
                $this->Session->setFlash(__('Unable to save CRM integration settings.'), 'flash', array('alert' => 'danger'));
                $activeTab = $provider;
            }
            elseif($provider === 'sms_gateway')
            {
                $submitAction = strtolower(trim((string)(isset($this->request->data['submit_action']) ? $this->request->data['submit_action'] : 'save')));
                $smsSaveData = $this->_normalizeUserSmsSettingsData((array)$this->request->data);
                $missingToken = '__crm_missing__';
                $postedSmsTemplateRaw = $this->_postedNestedValue((array)$this->request->data, 'default_sms_template_id', $missingToken);
                if($postedSmsTemplateRaw === $missingToken)
                {
                    $smsSaveData['default_sms_template_id'] = (int)$this->CrmIntegration->storedSmsTemplateIdForUser($currentUserId);
                }
                else
                {
                    $smsSaveData['default_sms_template_id'] = $this->_normalizePositiveId($postedSmsTemplateRaw);
                }
                $smsUserSettings = array('CrmUserSmsSetting' => array_merge($this->CrmIntegration->defaultUserSmsRow($currentUserId)['CrmUserSmsSetting'], $smsSaveData));
                $savedSmsTemplateId = !empty($smsUserSettings['CrmUserSmsSetting']['default_sms_template_id']) ? (int)$smsUserSettings['CrmUserSmsSetting']['default_sms_template_id'] : 0;
                if($submitAction === 'test')
                {
                    $effectiveSmsSettings = !empty($smsSaveData['use_global']) ? $globalSmsSettings : array('Smssetting' => $smsSaveData);
                    try
                    {
                        $this->_sendGatewayTestSms(
                            $effectiveSmsSettings,
                            isset($this->request->data['test_mobile']) ? $this->request->data['test_mobile'] : '',
                            isset($this->request->data['test_message']) ? $this->request->data['test_message'] : __('Test SMS from CRM Settings')
                        );
                        $this->Session->setFlash(__('Test SMS sent successfully.'), 'flash', array('alert' => 'success'));
                    }
                    catch(Exception $e)
                    {
                        $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                    }
                }
                else
                {
                    if($this->CrmIntegration->saveUserSmsRow($currentUserId, $smsSaveData))
                    {
                        $actualSavedTemplateId = (int)$this->CrmIntegration->storedSmsTemplateIdForUser($currentUserId);
                        if($actualSavedTemplateId !== (int)$smsSaveData['default_sms_template_id'])
                        {
                            $this->CrmIntegration->forceStoredSmsTemplateIdForUser($currentUserId, (int)$smsSaveData['default_sms_template_id']);
                            $actualSavedTemplateId = (int)$this->CrmIntegration->storedSmsTemplateIdForUser($currentUserId);
                        }
                        if($actualSavedTemplateId !== (int)$smsSaveData['default_sms_template_id'])
                        {
                            $this->Session->setFlash(__('SMS gateway settings were saved, but the selected default template could not be linked.'), 'flash', array('alert' => 'danger'));
                            $activeTab = 'sms_gateway';
                        }
                        else
                        {
                            $this->Session->setFlash(__('SMS gateway settings have been saved.'), 'flash', array('alert' => 'success'));
                            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'sms_gateway')));
                        }
                    }
                    else
                    {
                        $this->Session->setFlash(__('Unable to save SMS gateway settings.'), 'flash', array('alert' => 'danger'));
                    }
                }
                $activeTab = 'sms_gateway';
            }
            elseif($provider === 'email_gateway')
            {
                $submitAction = strtolower(trim((string)(isset($this->request->data['submit_action']) ? $this->request->data['submit_action'] : 'save')));
                $emailSaveData = $this->_normalizeUserEmailSettingsData((array)$this->request->data);
                $missingToken = '__crm_missing__';
                $postedEmailTemplateRaw = $this->_postedNestedValue((array)$this->request->data, 'default_email_template_id', $missingToken);
                if($postedEmailTemplateRaw === $missingToken)
                {
                    $emailSaveData['default_email_template_id'] = (int)$this->CrmIntegration->storedEmailTemplateIdForUser($currentUserId);
                }
                else
                {
                    $emailSaveData['default_email_template_id'] = $this->_normalizePositiveId($postedEmailTemplateRaw);
                }
                $emailUserSettings = array('CrmUserEmailSetting' => array_merge($this->CrmIntegration->defaultUserEmailRow($currentUserId)['CrmUserEmailSetting'], $emailSaveData));
                $savedEmailTemplateId = !empty($emailUserSettings['CrmUserEmailSetting']['default_email_template_id']) ? (int)$emailUserSettings['CrmUserEmailSetting']['default_email_template_id'] : 0;
                if($submitAction === 'test')
                {
                    $effectiveEmailSettings = !empty($emailSaveData['use_global']) ? $globalEmailSettings : array('Emailsetting' => $emailSaveData);
                    try
                    {
                        $this->_sendGatewayTestEmail(
                            $effectiveEmailSettings,
                            isset($this->request->data['test_email']) ? $this->request->data['test_email'] : '',
                            isset($this->request->data['test_subject']) ? $this->request->data['test_subject'] : __('CRM Gateway Test Email'),
                            isset($this->request->data['test_message']) ? $this->request->data['test_message'] : __('This is a CRM gateway test email.')
                        );
                        $this->Session->setFlash(__('Test email sent successfully.'), 'flash', array('alert' => 'success'));
                    }
                    catch(Exception $e)
                    {
                        $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                    }
                }
                else
                {
                    if($this->CrmIntegration->saveUserEmailRow($currentUserId, $emailSaveData))
                    {
                        $actualSavedTemplateId = (int)$this->CrmIntegration->storedEmailTemplateIdForUser($currentUserId);
                        if($actualSavedTemplateId !== (int)$emailSaveData['default_email_template_id'])
                        {
                            $this->CrmIntegration->forceStoredEmailTemplateIdForUser($currentUserId, (int)$emailSaveData['default_email_template_id']);
                            $actualSavedTemplateId = (int)$this->CrmIntegration->storedEmailTemplateIdForUser($currentUserId);
                        }
                        if($actualSavedTemplateId !== (int)$emailSaveData['default_email_template_id'])
                        {
                            $this->Session->setFlash(__('Email gateway settings were saved, but the selected default template could not be linked.'), 'flash', array('alert' => 'danger'));
                            $activeTab = 'email_gateway';
                        }
                        else
                        {
                            $this->Session->setFlash(__('Email gateway settings have been saved.'), 'flash', array('alert' => 'success'));
                            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'email_gateway')));
                        }
                    }
                    else
                    {
                        $this->Session->setFlash(__('Unable to save email gateway settings.'), 'flash', array('alert' => 'danger'));
                    }
                }
                $activeTab = 'email_gateway';
            }
            elseif($provider === 'google_calendar_global')
            {
                if(!$isSuperAdmin)
                {
                    $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
                    return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
                }
                $submitAction = strtolower(trim((string)(isset($this->request->data['submit_action']) ? $this->request->data['submit_action'] : 'save')));
                $globalGoogleSaveData = $this->_normalizeGoogleCalendarBaseData((array)$this->request->data);
                $googleGlobalSettings = array('CrmGoogleCalendarSetting' => array_merge($this->CrmGoogleCalendar->defaultGlobalRow()['CrmGoogleCalendarSetting'], $globalGoogleSaveData));
                if($submitAction === 'test')
                {
                    try
                    {
                        $calendarRows = (array)$this->CrmGoogleCalendar->fetchCalendarList('global', 0);
                        $this->Session->setFlash(sprintf(__('Google Calendar connection is working. Found %d calendars.'), count($calendarRows)), 'flash', array('alert' => 'success'));
                    }
                    catch(Exception $e)
                    {
                        $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                    }
                }
                else
                {
                    if($this->CrmGoogleCalendar->saveGlobalRow($globalGoogleSaveData))
                    {
                        $this->Session->setFlash(__('Global Google Calendar settings have been saved.'), 'flash', array('alert' => 'success'));
                        return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
                    }
                    $this->Session->setFlash(__('Unable to save global Google Calendar settings.'), 'flash', array('alert' => 'danger'));
                }
                $activeTab = 'google_calendar';
            }
            elseif($provider === 'google_calendar_user')
            {
                $submitAction = strtolower(trim((string)(isset($this->request->data['submit_action']) ? $this->request->data['submit_action'] : 'save')));
                $userGoogleSaveData = $this->_normalizeUserGoogleCalendarSettingsData((array)$this->request->data);
                $googleUserSettings = array('CrmUserGoogleCalendarSetting' => array_merge($this->CrmGoogleCalendar->defaultUserRow($currentUserId)['CrmUserGoogleCalendarSetting'], $userGoogleSaveData));
                if($submitAction === 'test')
                {
                    $testScopeType = !empty($userGoogleSaveData['use_global']) ? 'global' : 'user';
                    $testOwnerId = ($testScopeType === 'global') ? 0 : $currentUserId;
                    try
                    {
                        $calendarRows = (array)$this->CrmGoogleCalendar->fetchCalendarList($testScopeType, $testOwnerId);
                        $this->Session->setFlash(sprintf(__('Google Calendar connection is working. Found %d calendars.'), count($calendarRows)), 'flash', array('alert' => 'success'));
                    }
                    catch(Exception $e)
                    {
                        $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                    }
                }
                elseif($submitAction === 'sync')
                {
                    try
                    {
                        $syncResult = (array)$this->CrmGoogleCalendar->syncUserUpcomingItems($currentUserId);
                        $this->Session->setFlash(sprintf(__('Google Calendar sync completed. Leads: %d, Tasks: %d.'), (int)(isset($syncResult['lead_count']) ? $syncResult['lead_count'] : 0), (int)(isset($syncResult['calendar_count']) ? $syncResult['calendar_count'] : 0)), 'flash', array('alert' => 'success'));
                    }
                    catch(Exception $e)
                    {
                        $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                    }
                }
                else
                {
                    if($this->CrmGoogleCalendar->saveUserRow($currentUserId, $userGoogleSaveData))
                    {
                        $this->Session->setFlash(__('Google Calendar settings have been saved.'), 'flash', array('alert' => 'success'));
                        return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
                    }
                    $this->Session->setFlash(__('Unable to save Google Calendar settings.'), 'flash', array('alert' => 'danger'));
                }
                $googleEffectiveSettings = $this->CrmGoogleCalendar->effectiveSettingsForUser($currentUserId);
                $activeTab = 'google_calendar';
            }
        }

        $googleGlobalSettings = $this->CrmGoogleCalendar->findGlobalRow();
        $googleUserSettings = $this->CrmGoogleCalendar->findUserRow($currentUserId);
        $googleEffectiveSettings = $this->CrmGoogleCalendar->effectiveSettingsForUser($currentUserId);
        $smsUserSettings = $this->CrmIntegration->findUserSmsRow($currentUserId);
        $emailUserSettings = $this->CrmIntegration->findUserEmailRow($currentUserId);
        $savedSmsTemplateId = (int)$this->CrmIntegration->storedSmsTemplateIdForUser($currentUserId);
        $savedEmailTemplateId = (int)$this->CrmIntegration->storedEmailTemplateIdForUser($currentUserId);
        $smsTemplateOptions = $this->_leadTemplateBundle('sms', $currentUserId);
        $emailTemplateOptions = $this->_leadTemplateBundle('email', $currentUserId);
        if(!$this->_templateOptionExists($smsTemplateOptions, $savedSmsTemplateId))
        {
            $smsTemplateOptions = $this->_appendSavedTemplateOption($smsTemplateOptions, 'sms', $savedSmsTemplateId, $currentUserId);
        }
        if(!$this->_templateOptionExists($emailTemplateOptions, $savedEmailTemplateId))
        {
            $emailTemplateOptions = $this->_appendSavedTemplateOption($emailTemplateOptions, 'email', $savedEmailTemplateId, $currentUserId);
        }
        if(!$this->_templateOptionExists($smsTemplateOptions, $savedSmsTemplateId))
        {
            $savedSmsTemplateId = 0;
        }
        if(!$this->_templateOptionExists($emailTemplateOptions, $savedEmailTemplateId))
        {
            $savedEmailTemplateId = 0;
        }
        if(!isset($smsUserSettings['CrmUserSmsSetting']) || !is_array($smsUserSettings['CrmUserSmsSetting']))
        {
            $smsUserSettings['CrmUserSmsSetting'] = array();
        }
        if(!isset($emailUserSettings['CrmUserEmailSetting']) || !is_array($emailUserSettings['CrmUserEmailSetting']))
        {
            $emailUserSettings['CrmUserEmailSetting'] = array();
        }
        $smsUserSettings['CrmUserSmsSetting']['default_sms_template_id'] = $savedSmsTemplateId;
        $emailUserSettings['CrmUserEmailSetting']['default_email_template_id'] = $savedEmailTemplateId;
        if(!$this->request->is('post'))
        {
            $this->request->data['default_sms_template_id'] = $savedSmsTemplateId;
            $this->request->data['default_email_template_id'] = $savedEmailTemplateId;
        }
        $facebookSettings = $this->CrmIntegration->findProviderRow('facebook_leads');
        $whatsappSettings = $this->CrmIntegration->findProviderRow('whatsapp_cloud');
        $projectOptions = $this->_projectOptions();
        $userLevelOptions = $this->_userLevelOptions();
        $this->loadModel('Status');
        $statusOptions = (array)$this->Status->find('list', array(
            'fields' => array('Status.id', 'Status.name'),
            'order' => array('Status.name' => 'asc'),
            'recursive' => -1
        ));
        $facebookUserOptions = $this->_userOptions((int)$facebookSettings['CrmIntegration']['default_project_id'], (int)$facebookSettings['CrmIntegration']['default_ugroup_id']);
        $whatsappUserOptions = $this->_userOptions((int)$whatsappSettings['CrmIntegration']['default_project_id'], (int)$whatsappSettings['CrmIntegration']['default_ugroup_id']);
        $cakeConsolePath = str_replace('\\', '/', ROOT.DS.'lib'.DS.'Cake'.DS.'Console'.DS.'cake.php');
        $appPath = rtrim(str_replace('\\', '/', APP), '/');
        $smsCronCommand = 'php '.$cakeConsolePath.' LeadSmsSchedule run -app '.$appPath;
        $smsCronSample = '*/5 * * * * '.$smsCronCommand.' >/dev/null 2>&1';
        $smsCronToken = sha1((string)Configure::read('Security.salt').'|lead_sms_schedule');
        $smsCronUrl = Router::url(array(
            'controller' => 'Leads',
            'action' => 'runscheduledsmscron',
            '?' => array('token' => $smsCronToken, 'limit' => 200)
        ), true);
        $smsCronUrlCommand = 'wget -q -O /dev/null "'.$smsCronUrl.'"';
        $smsCronUrlSample = '*/5 * * * * '.$smsCronUrlCommand;

        $this->set(compact(
            'providerOptions',
            'activeTab',
            'facebookSettings',
            'whatsappSettings',
            'smsUserSettings',
            'emailUserSettings',
            'smsTemplateOptions',
            'emailTemplateOptions',
            'globalSmsSettings',
            'globalEmailSettings',
            'googleGlobalSettings',
            'googleUserSettings',
            'googleEffectiveSettings',
            'currentUserId',
            'isSuperAdmin',
            'projectOptions',
            'userLevelOptions',
            'statusOptions',
            'facebookUserOptions',
            'whatsappUserOptions',
            'smsCronUrl',
            'smsCronUrlSample',
            'smsCronCommand',
            'smsCronSample'
        ));
        $this->set('googleCalendarCallbackUrl', $this->_googleCalendarCallbackUrl());
        $this->set('facebookWebhookUrl', $this->_providerWebhookUrl('facebook_leads'));
        $this->set('whatsappWebhookUrl', $this->_providerWebhookUrl('whatsapp_cloud'));
    }

    public function googlecalendarconnect($scope = 'user')
    {
        $scope = strtolower(trim((string)$scope));
        if(!in_array($scope, array('global', 'user'), true))
        {
            $scope = 'user';
        }
        $currentUserId = $this->_currentAdminUserId();
        if($scope === 'global' && !$this->_isSuperAdminSession())
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
        }
        $scopeOwnerId = ($scope === 'global') ? 0 : $currentUserId;
        $scopeRow = ($scope === 'global') ? $this->CrmGoogleCalendar->findGlobalRow() : $this->CrmGoogleCalendar->findUserRow($currentUserId);
        $scopeSettings = ($scope === 'global') ? (array)$scopeRow['CrmGoogleCalendarSetting'] : (array)$scopeRow['CrmUserGoogleCalendarSetting'];
        if(!$this->CrmGoogleCalendar->settingsLookConfigured($scopeSettings))
        {
            $this->Session->setFlash(__('Please save Google Client ID and Client Secret first.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
        }
        $stateToken = sha1($currentUserId.'|'.$scope.'|'.microtime(true).'|'.mt_rand(1000, 999999));
        $this->Session->write('CrmGoogleCalendarOauth', array(
            'state' => $stateToken,
            'scope' => $scope,
            'owner_id' => $scopeOwnerId,
            'user_id' => $currentUserId,
            'created' => time()
        ));
        try
        {
            $connectUrl = $this->CrmGoogleCalendar->buildAuthUrl($scopeSettings, $this->_googleCalendarCallbackUrl(), $stateToken);
            return $this->redirect($connectUrl);
        }
        catch(Exception $e)
        {
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
    }

    public function googlecalendarcallback()
    {
        $oauthState = (array)$this->Session->read('CrmGoogleCalendarOauth');
        $this->Session->delete('CrmGoogleCalendarOauth');
        $stateToken = isset($this->request->query['state']) ? trim((string)$this->request->query['state']) : '';
        if(empty($oauthState) || $stateToken === '' || empty($oauthState['state']) || !hash_equals((string)$oauthState['state'], $stateToken))
        {
            $this->Session->setFlash(__('Google Calendar callback state is invalid or expired.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
        }
        if(!empty($this->request->query['error']))
        {
            $errorText = trim((string)(isset($this->request->query['error_description']) ? $this->request->query['error_description'] : $this->request->query['error']));
            $this->Session->setFlash($errorText !== '' ? $errorText : __('Google Calendar authorization was cancelled.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
        }
        $code = isset($this->request->query['code']) ? trim((string)$this->request->query['code']) : '';
        if($code === '')
        {
            $this->Session->setFlash(__('Missing Google authorization code.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
        }
        $scope = isset($oauthState['scope']) ? strtolower(trim((string)$oauthState['scope'])) : 'user';
        $ownerId = !empty($oauthState['owner_id']) ? (int)$oauthState['owner_id'] : 0;
        if($scope === 'global' && !$this->_isSuperAdminSession())
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
        }
        try
        {
            $scopeSettings = (array)$this->CrmGoogleCalendar->exchangeCode($scope, $ownerId, $code, $this->_googleCalendarCallbackUrl());
            $accountEmail = trim((string)(isset($scopeSettings['google_email']) ? $scopeSettings['google_email'] : ''));
            $message = __('Google Calendar account connected successfully.');
            if($accountEmail !== '')
            {
                $message .= ' '.__('Account').': '.$accountEmail;
            }
            $this->Session->setFlash($message, 'flash', array('alert' => 'success'));
        }
        catch(Exception $e)
        {
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
    }

    public function googlecalendardisconnect($scope = 'user')
    {
        $scope = strtolower(trim((string)$scope));
        if(!in_array($scope, array('global', 'user'), true))
        {
            $scope = 'user';
        }
        if($scope === 'global' && !$this->_isSuperAdminSession())
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
        }
        $ownerId = ($scope === 'global') ? 0 : $this->_currentAdminUserId();
        if($this->CrmGoogleCalendar->disconnect($scope, $ownerId))
        {
            $this->Session->setFlash(__('Google Calendar account has been disconnected.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to disconnect Google Calendar account.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index', '?' => array('tab' => 'google_calendar')));
    }

    public function bulkimport()
    {
        if(!$this->_canUseCrmBulkImport())
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        if(isset($this->request->query['download']) && strtolower(trim((string)$this->request->query['download'])) === 'sample')
        {
            $this->autoRender = false;
            $filename = 'crm_bulk_import_sample_'.date('Ymd_His').'.csv';
            header("Content-Type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");
            $out = fopen('php://output', 'w');
            fputcsv($out, array('lead_name','lead_mobile','lead_email','lead_address','lead_city','lead_budget','lead_language','lead_source','source_form_id','source_ad_id','utm_campaign','last_contact_date','status','follow_up','remarks','property'));
            fputcsv($out, array('Ahmad Raza','923001234567','ahmad@example.com','Johar Town Lahore','Lahore','3500000','Urdu','Facebook','123456789','987654321','Ramzan Offer','2026-04-01','New','2026-04-02 03:00 PM','Interested in 3 marla file','1 Marla Plot'));
            fputcsv($out, array('Sara Khan','923009876543','sara@example.com','Bahria Town Lahore','Lahore','5200000','English','Referral','','','Summer Walk-In','2026-04-01','Contacted','2026-04-05 11:30 AM','Asked for payment plan',''));
            fclose($out);
            return;
        }

        $this->_ensureLeadImportSchema();
        $this->loadModel('Lead');
        $this->loadModel('User');

        $isSuperAdmin = $this->_isSuperAdminSession();
        $projectOptions = $this->_projectOptions();
        $userLevelOptions = $this->_userLevelOptions();
        $selectedProjectId = $isSuperAdmin ? 0 : (int)$this->agencyId;
        $selectedUserLevelId = 0;
        $selectedUserIds = array();
        $autoCreateContact = 0;

        if($this->request->is('post'))
        {
            if(empty($this->userPermissionArr['save_right']))
            {
                $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'bulkimport'));
            }

            $form = isset($this->request->data['CrmBulkImport']) ? (array)$this->request->data['CrmBulkImport'] : array();
            $selectedProjectId = $isSuperAdmin ? $this->_normalizePositiveId(isset($form['project_id']) ? $form['project_id'] : 0) : (int)$this->agencyId;
            if(!$isSuperAdmin && (int)$this->agencyId > 0)
            {
                $selectedProjectId = (int)$this->agencyId;
            }
            if($selectedProjectId > 0 && !isset($projectOptions[$selectedProjectId]))
            {
                $selectedProjectId = 0;
            }

            $selectedUserLevelId = $this->_normalizePositiveId(isset($form['ugroup_id']) ? $form['ugroup_id'] : 0);
            if($selectedUserLevelId > 0 && !isset($userLevelOptions[$selectedUserLevelId]))
            {
                $selectedUserLevelId = 0;
            }

            $rawUserIds = isset($form['user_ids']) ? (array)$form['user_ids'] : array();
            $selectedUserIds = array();
            foreach($rawUserIds as $rawUserId)
            {
                $userId = (int)$rawUserId;
                if($userId > 0)
                {
                    $selectedUserIds[$userId] = $userId;
                }
            }
            $selectedUserIds = array_values($selectedUserIds);
            $autoCreateContact = !empty($form['create_contact']) ? 1 : 0;

            $selectedUserOptions = ((int)$selectedProjectId > 0) ? $this->_userOptions($selectedProjectId, $selectedUserLevelId) : array();
            if(!empty($selectedUserIds))
            {
                $selectedUserIds = array_values(array_intersect($selectedUserIds, array_map('intval', array_keys((array)$selectedUserOptions))));
            }

            $upload = isset($form['import_file']) ? (array)$form['import_file'] : array();
            $extension = strtolower(pathinfo((string)(isset($upload['name']) ? $upload['name'] : ''), PATHINFO_EXTENSION));

            if($selectedProjectId <= 0)
            {
                $this->Session->setFlash(__('Please select agency first.'), 'flash', array('alert' => 'danger'));
            }
            elseif($selectedUserLevelId <= 0)
            {
                $this->Session->setFlash(__('Please select user level first.'), 'flash', array('alert' => 'danger'));
            }
            elseif(empty($selectedUserIds))
            {
                $this->Session->setFlash(__('Please select at least one user for distribution.'), 'flash', array('alert' => 'danger'));
            }
            elseif(empty($upload['tmp_name']))
            {
                $this->Session->setFlash(__('Please upload import file.'), 'flash', array('alert' => 'danger'));
            }
            elseif(!in_array($extension, array('csv', 'xlsx'), true))
            {
                $this->Session->setFlash(__('Supported import files are CSV and XLSX only.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $rows = $this->_parseLeadImportRows($upload['tmp_name'], isset($upload['name']) ? $upload['name'] : '');
                if(empty($rows) || count($rows) < 2)
                {
                    $this->Session->setFlash(__('Import file is empty or invalid. Use sample format.'), 'flash', array('alert' => 'danger'));
                }
                else
                {
                    $headerMap = $this->_mapLeadImportHeaders($rows[0]);
                    if(empty($headerMap))
                    {
                        $this->Session->setFlash(__('Header row is invalid. Please use sample file headers.'), 'flash', array('alert' => 'danger'));
                    }
                    else
                    {
                        $preparedRows = array();
                        for($i = 1; $i < count($rows); $i++)
                        {
                            $line = (array)$rows[$i];
                            $lineData = array();
                            foreach($headerMap as $colIndex => $fieldKey)
                            {
                                $lineData[$fieldKey] = isset($line[$colIndex]) ? trim((string)$line[$colIndex]) : '';
                            }
                            if(
                                trim((string)(isset($lineData['lead_name']) ? $lineData['lead_name'] : '')) === '' &&
                                trim((string)(isset($lineData['lead_mobile']) ? $lineData['lead_mobile'] : '')) === '' &&
                                trim((string)(isset($lineData['lead_email']) ? $lineData['lead_email'] : '')) === '' &&
                                (int)(isset($lineData['client_id']) ? $lineData['client_id'] : 0) <= 0
                            )
                            {
                                continue;
                            }
                            $preparedRows[] = $lineData;
                        }

                        if(empty($preparedRows))
                        {
                            $this->Session->setFlash(__('No valid lead rows found in file.'), 'flash', array('alert' => 'danger'));
                        }
                        else
                        {
                            shuffle($preparedRows);
                            $distributionUserIds = array_values($selectedUserIds);
                            shuffle($distributionUserIds);
                            $defaultStatusId = $this->_defaultLeadStatusId();
                            $imported = 0;
                            $failed = 0;
                            $cursor = 0;
                            $perUserCounts = array();
                            foreach($distributionUserIds as $distributionUserId)
                            {
                                $perUserCounts[(int)$distributionUserId] = 0;
                            }

                            foreach($preparedRows as $lineData)
                            {
                                $assignedUserId = isset($distributionUserIds[$cursor % count($distributionUserIds)]) ? (int)$distributionUserIds[$cursor % count($distributionUserIds)] : 0;
                                $cursor++;
                                if($assignedUserId <= 0)
                                {
                                    $failed++;
                                    continue;
                                }

                                $leadPayloadInput = array(
                                    'lead_name' => isset($lineData['lead_name']) ? $lineData['lead_name'] : '',
                                    'lead_mobile' => isset($lineData['lead_mobile']) ? $lineData['lead_mobile'] : '',
                                    'lead_email' => isset($lineData['lead_email']) ? $lineData['lead_email'] : '',
                                    'lead_address' => isset($lineData['lead_address']) ? $lineData['lead_address'] : '',
                                    'lead_source' => isset($lineData['lead_source']) ? $lineData['lead_source'] : '',
                                    'last_contact_date' => isset($lineData['last_contact_date']) ? $lineData['last_contact_date'] : '',
                                    'status_id' => isset($lineData['status']) ? $lineData['status'] : $defaultStatusId,
                                    'follow_up' => isset($lineData['follow_up']) ? $lineData['follow_up'] : '',
                                    'remarks' => isset($lineData['remarks']) ? $lineData['remarks'] : '',
                                    'property_id' => isset($lineData['property']) ? $lineData['property'] : '',
                                    'client_id' => isset($lineData['client_id']) ? $lineData['client_id'] : 0,
                                    'create_contact' => $autoCreateContact
                                );

                                $payload = $this->_buildImportedLeadPayload($leadPayloadInput, $selectedProjectId, $assignedUserId, $defaultStatusId);
                                $automationResult = $this->_applyCrmAutomationToLeadPayload($payload, 'import', 0);
                                $payload = isset($automationResult['payload']) ? (array)$automationResult['payload'] : $payload;
                                if(
                                    trim((string)$payload['lead_name']) === '' &&
                                    trim((string)$payload['lead_mobile']) === '' &&
                                    trim((string)$payload['lead_email']) === '' &&
                                    (int)$payload['client_id'] <= 0
                                )
                                {
                                    $failed++;
                                    continue;
                                }
                                if(empty($automationResult['success']))
                                {
                                    $failed++;
                                    continue;
                                }
                                if($autoCreateContact === 1 && (int)$payload['client_id'] <= 0)
                                {
                                    $failed++;
                                    continue;
                                }

                                $this->Lead->create();
                                $saveOk = false;
                                try
                                {
                                    $saveOk = (bool)$this->Lead->save(array('Lead' => $payload), array('validate' => false));
                                }
                                catch(Exception $e)
                                {
                                    $saveOk = false;
                                }

                                if($saveOk)
                                {
                                    $imported++;
                                    if(isset($perUserCounts[$assignedUserId]))
                                    {
                                        $perUserCounts[$assignedUserId]++;
                                    }
                                }
                                else
                                {
                                    $failed++;
                                }
                            }

                            if($imported > 0)
                            {
                                $summaryParts = array();
                                foreach($perUserCounts as $userId => $count)
                                {
                                    $userLabel = isset($selectedUserOptions[$userId]) ? (string)$selectedUserOptions[$userId] : ('User #'.$userId);
                                    $summaryParts[] = $userLabel.': '.$count;
                                }
                                $message = __('Leads imported and distributed successfully: ').$imported;
                                if($failed > 0)
                                {
                                    $message .= ' | '.__('Failed rows: ').$failed;
                                }
                                if(!empty($summaryParts))
                                {
                                    $message .= ' | '.implode(' | ', $summaryParts);
                                }
                                $this->Session->setFlash($message, 'flash', array('alert' => 'success'));
                                return $this->redirect(array('action' => 'bulkimport'));
                            }
                            $this->Session->setFlash(__('No leads imported. Please check your file values.'), 'flash', array('alert' => 'danger'));
                        }
                    }
                }
            }
        }

        if($selectedProjectId > 0 && !isset($projectOptions[$selectedProjectId]))
        {
            $selectedProjectId = 0;
        }
        $selectedUserOptions = ((int)$selectedProjectId > 0) ? $this->_userOptions($selectedProjectId, $selectedUserLevelId) : array();
        if(!empty($selectedUserIds))
        {
            $selectedUserIds = array_values(array_intersect($selectedUserIds, array_map('intval', array_keys((array)$selectedUserOptions))));
        }
        $currentImportAgencyName = '';
        if((int)$selectedProjectId > 0 && !empty($projectOptions[$selectedProjectId]))
        {
            $currentImportAgencyName = (string)$projectOptions[$selectedProjectId];
        }

        $this->set(compact(
            'projectOptions',
            'userLevelOptions',
            'selectedProjectId',
            'selectedUserLevelId',
            'selectedUserIds',
            'selectedUserOptions',
            'autoCreateContact',
            'currentImportAgencyName',
            'isSuperAdmin'
        ));
    }

    public function routes($id = null)
    {
        $providerOptions = $this->_providerOptions();
        $projectOptions = $this->_projectOptions();
        $userLevelOptions = $this->_userLevelOptions();
        $routeTypeOptionsMap = $this->_routeTypeOptionsMap();
        $this->loadModel('Status');
        $statusOptions = (array)$this->Status->find('list', array(
            'fields' => array('Status.id', 'Status.name'),
            'order' => array('Status.name' => 'asc'),
            'recursive' => -1
        ));
        $this->loadModel('CrmIntegrationRoute');

        $editId = $this->_normalizePositiveId($id);
        if($editId <= 0 && isset($this->request->query['edit']))
        {
            $editId = $this->_normalizePositiveId($this->request->query['edit']);
        }

        $routeForm = $this->_defaultRouteFormData();
        if($editId > 0)
        {
            $editRow = $this->_routeRowById($editId);
            if(!empty($editRow['CrmIntegrationRoute']))
            {
                $routeForm = array_merge($routeForm, (array)$editRow['CrmIntegrationRoute']);
            }
        }

        if($this->request->is('post'))
        {
            $routeForm = $this->_normalizeRouteData((array)$this->request->data);
            $validationErrors = $this->_validateRouteData($routeForm);
            if(empty($validationErrors))
            {
                $saveData = $routeForm;
                if(trim((string)$saveData['title']) === '')
                {
                    $saveData['title'] = $saveData['external_id'];
                }
                $this->CrmIntegrationRoute->create();
                if((int)$saveData['id'] > 0)
                {
                    $this->CrmIntegrationRoute->id = (int)$saveData['id'];
                }
                if($this->CrmIntegrationRoute->save(array('CrmIntegrationRoute' => $saveData), array('validate' => false)))
                {
                    $this->Session->setFlash(__('CRM source routing has been saved.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'routes'));
                }
                $validationErrors[] = __('Unable to save CRM source routing.');
            }
            $this->Session->setFlash(implode(' ', $validationErrors), 'flash', array('alert' => 'danger'));
        }

        $routeUserOptions = $this->_userOptions((int)$routeForm['project_id'], (int)$routeForm['ugroup_id']);
        $routeRows = (array)$this->CrmIntegrationRoute->find('all', array(
            'order' => array(
                'CrmIntegrationRoute.provider' => 'asc',
                'CrmIntegrationRoute.match_type' => 'asc',
                'CrmIntegrationRoute.title' => 'asc',
                'CrmIntegrationRoute.id' => 'desc'
            ),
            'recursive' => 0
        ));

        $this->set(compact(
            'providerOptions',
            'projectOptions',
            'userLevelOptions',
            'statusOptions',
            'routeTypeOptionsMap',
            'routeForm',
            'routeUserOptions',
            'routeRows'
        ));
    }

    public function routedelete($id = null)
    {
        $id = $this->_normalizePositiveId($id);
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid CRM route.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'routes'));
        }
        $this->loadModel('CrmIntegrationRoute');
        if($this->CrmIntegrationRoute->delete($id, false))
        {
            $this->Session->setFlash(__('CRM route deleted successfully.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to delete CRM route.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'routes'));
    }

    public function logs()
    {
        $providerOptions = $this->_providerOptions();
        $selectedProvider = isset($this->request->query['provider']) ? strtolower(trim((string)$this->request->query['provider'])) : '';
        $conditions = array();
        if($selectedProvider !== '' && isset($providerOptions[$selectedProvider]))
        {
            $conditions['CrmIntegrationLog.provider'] = $selectedProvider;
        }
        $this->loadModel('CrmIntegrationLog');
        $this->Paginator->settings = array(
            'CrmIntegrationLog' => array(
                'conditions' => $conditions,
                'order' => array('CrmIntegrationLog.id' => 'desc'),
                'limit' => 25,
                'maxLimit' => 100,
                'recursive' => 0
            )
        );
        $logRows = (array)$this->Paginator->paginate('CrmIntegrationLog');
        $this->set(compact('providerOptions', 'selectedProvider', 'logRows'));
    }
}
?>

