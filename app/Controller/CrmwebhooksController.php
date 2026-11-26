<?php
App::uses('AppController', 'Controller');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

class CrmwebhooksController extends AppController
{
    public $uses = array('CrmIntegration', 'CrmIntegrationRoute', 'CrmIntegrationLog', 'Lead', 'User', 'Status', 'Category');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->autoRender = false;
        $this->layout = false;
        $this->CrmIntegration->ensureSchema();
    }

    protected function _writePlainResponse($body, $status = 200)
    {
        $this->response->statusCode((int)$status);
        $this->response->type('text/plain');
        $this->response->body((string)$body);
        return $this->response;
    }

    protected function _writeJsonResponse($payload = array(), $status = 200)
    {
        $this->response->statusCode((int)$status);
        $this->response->type('json');
        $this->response->body(json_encode((array)$payload));
        return $this->response;
    }

    protected function _providerSettings($provider)
    {
        return (array)$this->CrmIntegration->findProviderRow($provider);
    }

    protected function _providerLabel($provider)
    {
        $options = (array)$this->CrmIntegration->providerOptions();
        return isset($options[$provider]) ? (string)$options[$provider] : ucwords(str_replace('_', ' ', (string)$provider));
    }

    protected function _logEvent($provider, $eventType, $externalRef, $status, $message, $payload = array(), $assignment = array(), $leadId = 0)
    {
        $assignment = (array)$assignment;
        $saveData = array(
            'provider' => (string)$provider,
            'event_type' => (string)$eventType,
            'external_ref' => trim((string)$externalRef),
            'project_id' => !empty($assignment['project_id']) ? (int)$assignment['project_id'] : null,
            'user_id' => !empty($assignment['user_id']) ? (int)$assignment['user_id'] : null,
            'lead_id' => ((int)$leadId > 0 ? (int)$leadId : null),
            'status' => trim((string)$status),
            'message' => trim((string)$message),
            'payload_json' => (!empty($payload) ? json_encode($payload) : null)
        );
        $this->CrmIntegrationLog->create();
        $this->CrmIntegrationLog->save(array('CrmIntegrationLog' => $saveData), array('validate' => false));
    }

    protected function _alreadyProcessedExternalRef($provider, $externalRef)
    {
        $externalRef = trim((string)$externalRef);
        if($externalRef === '')
        {
            return false;
        }
        $count = (int)$this->CrmIntegrationLog->find('count', array(
            'conditions' => array(
                'CrmIntegrationLog.provider' => (string)$provider,
                'CrmIntegrationLog.external_ref' => $externalRef,
                'CrmIntegrationLog.status' => array('saved', 'updated_existing', 'duplicate')
            ),
            'recursive' => -1
        ));
        return ($count > 0);
    }

    protected function _defaultLeadStatusId()
    {
        $row = (array)$this->Status->find('first', array(
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
        $row = (array)$this->Status->find('first', array(
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

    protected function _sanitizeMobile($mobileValue)
    {
        $mobileValue = trim((string)$mobileValue);
        return preg_replace('/\s+/', '', $mobileValue);
    }

    protected function _normalizeEmail($emailValue)
    {
        return strtolower(trim((string)$emailValue));
    }

    protected function _leadNormalizeDateInput($rawValue)
    {
        $rawValue = trim((string)$rawValue);
        if($rawValue === '')
        {
            return '';
        }
        $knownFormats = array('Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y', 'd.m.Y', 'm/d/Y', 'm-d-Y', 'Y-m-d H:i:s', 'Y-m-d H:i', 'd-m-Y H:i', 'd/m/Y H:i', 'm/d/Y H:i');
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

    protected function _safeFollowUpDateTime($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return null;
        }
        $knownFormats = array('Y-m-d H:i:s', 'Y-m-d H:i', 'Y/m/d H:i:s', 'Y/m/d H:i', 'd-m-Y H:i:s', 'd-m-Y H:i', 'd/m/Y H:i:s', 'd/m/Y H:i', 'm/d/Y H:i:s', 'm/d/Y H:i', 'd-m-Y h:i A', 'd/m/Y h:i A', 'm/d/Y h:i A', 'Y-m-d h:i A', 'Y/m/d h:i A');
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
            $row = (array)$this->Contact->find('first', array(
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
        if(trim((string)$mobileNo) !== '')
        {
            $orConditions[] = array('Contact.mobile' => $mobileNo);
            $orConditions[] = array('Contact.phone' => $mobileNo);
        }
        if(trim((string)$emailAddress) !== '')
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
        $row = (array)$this->Contact->find('first', array(
            'fields' => array('Contact.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        return !empty($row['Contact']['id']) ? (int)$row['Contact']['id'] : 0;
    }

    protected function _createContactFromLeadData($projectId, $userId, $leadName, $mobileNo, $emailAddress, $leadAddress = '')
    {
        $this->loadModel('Admin.Contact');
        $category = (array)$this->Category->find('first', array(
            'fields' => array('Category.id'),
            'conditions' => array('Category.type' => null),
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

    protected function _buildLeadPayload($formData, $assignment)
    {
        $assignment = (array)$assignment;
        $projectId = !empty($assignment['project_id']) ? (int)$assignment['project_id'] : 0;
        $userId = !empty($assignment['user_id']) ? (int)$assignment['user_id'] : 0;
        $leadName = trim((string)(isset($formData['lead_name']) ? $formData['lead_name'] : ''));
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
        $lastContactDate = $this->_leadNormalizeDateInput(isset($formData['last_contact_date']) ? $formData['last_contact_date'] : '');
        $followUp = $this->_safeFollowUpDateTime(isset($formData['follow_up']) ? $formData['follow_up'] : '');
        $remarks = trim((string)(isset($formData['remarks']) ? $formData['remarks'] : ''));
        $statusId = $this->_resolveLeadStatusId(isset($formData['status_id']) ? $formData['status_id'] : (!empty($assignment['status_id']) ? $assignment['status_id'] : ''));
        $clientId = 0;
        $autoCreateContact = !empty($assignment['auto_create_contact']) ? 1 : 0;
        if($autoCreateContact === 1)
        {
            $clientId = $this->_findContactByLeadData($projectId, $leadMobile, $leadEmail, isset($formData['client_id']) ? $formData['client_id'] : 0);
            if($clientId <= 0 && $leadName !== '')
            {
                $clientId = $this->_createContactFromLeadData($projectId, $userId, $leadName, $leadMobile, $leadEmail, $leadAddress);
            }
        }
        return array(
            'project_id' => $projectId,
            'user_id' => $userId,
            'client_id' => ($clientId > 0 ? $clientId : null),
            'property_id' => null,
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
            'lead_temperature' => 'warm'
        );
    }

    protected function _applyCrmAutomationToLeadPayload($payload = array(), $scope = 'webhook', $excludeLeadId = 0)
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

    protected function _findExistingLeadByContact($projectId, $mobileNo, $emailAddress)
    {
        $orConditions = array();
        $mobileNo = trim((string)$mobileNo);
        $emailAddress = trim((string)$emailAddress);
        if($mobileNo !== '')
        {
            $orConditions[] = array('Lead.lead_mobile' => $mobileNo);
        }
        if($emailAddress !== '')
        {
            $orConditions[] = array('LOWER(Lead.lead_email)' => strtolower($emailAddress));
        }
        if(empty($orConditions))
        {
            return array();
        }
        return (array)$this->Lead->find('first', array(
            'fields' => array('Lead.id', 'Lead.user_id', 'Lead.status_id', 'Lead.remarks', 'Lead.lead_name', 'Lead.lead_source'),
            'conditions' => array(
                'Lead.project_id' => (int)$projectId,
                'OR' => $orConditions
            ),
            'order' => array('Lead.id' => 'desc'),
            'recursive' => -1
        ));
    }

    protected function _appendRemarkText($existingRemark, $appendText)
    {
        $existingRemark = trim((string)$existingRemark);
        $appendText = trim((string)$appendText);
        if($appendText === '')
        {
            return $existingRemark;
        }
        if($existingRemark === '')
        {
            return $appendText;
        }
        if(stripos($existingRemark, $appendText) !== false)
        {
            return $existingRemark;
        }
        return $existingRemark."\n\n".$appendText;
    }

    protected function _resolveRoute($provider, $candidates = array())
    {
        foreach((array)$candidates as $candidate)
        {
            $matchType = isset($candidate['match_type']) ? trim((string)$candidate['match_type']) : '';
            $externalId = isset($candidate['external_id']) ? trim((string)$candidate['external_id']) : '';
            if($matchType === '' || $externalId === '')
            {
                continue;
            }
            $row = (array)$this->CrmIntegrationRoute->find('first', array(
                'conditions' => array(
                    'CrmIntegrationRoute.provider' => (string)$provider,
                    'CrmIntegrationRoute.match_type' => $matchType,
                    'CrmIntegrationRoute.external_id' => $externalId,
                    'CrmIntegrationRoute.is_active' => 1
                ),
                'order' => array('CrmIntegrationRoute.id' => 'desc'),
                'recursive' => -1
            ));
            if(!empty($row['CrmIntegrationRoute']['id']))
            {
                return $row;
            }
        }
        return array();
    }

    protected function _resolveAssignableUserId($projectId, $ugroupId, $requestedUserId)
    {
        $conditions = array($this->User->assignableScopeConditions((int)$projectId, 'User'));
        if((int)$ugroupId > 0)
        {
            $conditions[] = array('User.ugroup_id' => (int)$ugroupId);
        }
        $requestedUserId = (int)$requestedUserId;
        if($requestedUserId > 0)
        {
            $requestedConditions = $conditions;
            $requestedConditions[] = array('User.id' => $requestedUserId);
            $requestedRow = (array)$this->User->find('first', array(
                'fields' => array('User.id'),
                'conditions' => $requestedConditions,
                'recursive' => -1
            ));
            if(!empty($requestedRow['User']['id']))
            {
                return (int)$requestedRow['User']['id'];
            }
        }
        $fallbackRow = (array)$this->User->find('first', array(
            'fields' => array('User.id'),
            'conditions' => $conditions,
            'order' => array('User.id' => 'asc'),
            'recursive' => -1
        ));
        return !empty($fallbackRow['User']['id']) ? (int)$fallbackRow['User']['id'] : 0;
    }

    protected function _resolveAssignment($provider, $settingsRow, $candidates = array())
    {
        $settings = !empty($settingsRow['CrmIntegration']) ? (array)$settingsRow['CrmIntegration'] : array();
        $routeRow = $this->_resolveRoute($provider, $candidates);
        $route = !empty($routeRow['CrmIntegrationRoute']) ? (array)$routeRow['CrmIntegrationRoute'] : array();
        $projectId = !empty($route['project_id']) ? (int)$route['project_id'] : (!empty($settings['default_project_id']) ? (int)$settings['default_project_id'] : 0);
        $ugroupId = !empty($route['ugroup_id']) ? (int)$route['ugroup_id'] : (!empty($settings['default_ugroup_id']) ? (int)$settings['default_ugroup_id'] : 0);
        $requestedUserId = !empty($route['user_id']) ? (int)$route['user_id'] : (!empty($settings['default_user_id']) ? (int)$settings['default_user_id'] : 0);
        $userId = $this->_resolveAssignableUserId($projectId, $ugroupId, $requestedUserId);
        $statusId = !empty($route['status_id']) ? (int)$route['status_id'] : (!empty($settings['default_status_id']) ? (int)$settings['default_status_id'] : $this->_defaultLeadStatusId());
        $leadSource = trim((string)(!empty($route['lead_source']) ? $route['lead_source'] : (!empty($settings['default_lead_source']) ? $settings['default_lead_source'] : $this->_providerLabel($provider))));
        $autoCreateContact = !empty($routeRow['CrmIntegrationRoute']['id']) ? (!empty($route['auto_create_contact']) ? 1 : 0) : (!empty($settings['auto_create_contact']) ? 1 : 0);
        return array(
            'project_id' => $projectId,
            'ugroup_id' => $ugroupId,
            'user_id' => $userId,
            'status_id' => $statusId,
            'lead_source' => $leadSource,
            'auto_create_contact' => $autoCreateContact,
            'route' => $route
        );
    }

    protected function _httpJsonGet($url, $headers = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        if(!empty($headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, (array)$headers);
        }
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = json_decode((string)$body, true);
        return array(
            'success' => ($error === '' && $status >= 200 && $status < 300 && is_array($json)),
            'status' => $status,
            'error' => $error,
            'body' => $body,
            'json' => is_array($json) ? $json : array()
        );
    }

    protected function _facebookFetchLead($leadgenId, $settingsRow)
    {
        $settings = !empty($settingsRow['CrmIntegration']) ? (array)$settingsRow['CrmIntegration'] : array();
        $accessToken = trim((string)(isset($settings['access_token']) ? $settings['access_token'] : ''));
        if($accessToken === '')
        {
            return array('success' => false, 'message' => 'Facebook access token is missing');
        }
        $params = array(
            'access_token' => $accessToken,
            'fields' => 'id,created_time,form_id,ad_id,field_data'
        );
        $appSecret = trim((string)(isset($settings['app_secret']) ? $settings['app_secret'] : ''));
        if($appSecret !== '')
        {
            $params['appsecret_proof'] = hash_hmac('sha256', $accessToken, $appSecret);
        }
        $url = 'https://graph.facebook.com/v21.0/'.rawurlencode((string)$leadgenId).'/?'.http_build_query($params);
        $result = $this->_httpJsonGet($url);
        if(!$result['success'])
        {
            $message = 'Facebook lead fetch failed';
            if(!empty($result['json']['error']['message']))
            {
                $message = (string)$result['json']['error']['message'];
            }
            elseif(trim((string)$result['error']) !== '')
            {
                $message = trim((string)$result['error']);
            }
            return array('success' => false, 'message' => $message, 'raw' => $result);
        }
        return array('success' => true, 'data' => (array)$result['json']);
    }

    protected function _facebookFieldMap($fieldData = array())
    {
        $map = array();
        foreach((array)$fieldData as $fieldRow)
        {
            $fieldName = !empty($fieldRow['name']) ? strtolower(trim((string)$fieldRow['name'])) : '';
            if($fieldName === '')
            {
                continue;
            }
            $valueText = '';
            if(isset($fieldRow['values']) && is_array($fieldRow['values']))
            {
                $cleanValues = array();
                foreach($fieldRow['values'] as $fieldValue)
                {
                    $cleanValue = trim((string)$fieldValue);
                    if($cleanValue !== '')
                    {
                        $cleanValues[] = $cleanValue;
                    }
                }
                $valueText = implode(', ', $cleanValues);
            }
            $map[$fieldName] = $valueText;
        }
        return $map;
    }

    protected function _facebookValueFromAliases($fieldMap, $aliases = array())
    {
        foreach((array)$aliases as $alias)
        {
            $alias = strtolower(trim((string)$alias));
            if($alias !== '' && !empty($fieldMap[$alias]))
            {
                return trim((string)$fieldMap[$alias]);
            }
        }
        return '';
    }

    protected function _extractWhatsAppMessageText($message = array())
    {
        $message = (array)$message;
        $type = !empty($message['type']) ? strtolower(trim((string)$message['type'])) : '';
        if($type === 'text' && !empty($message['text']['body']))
        {
            return trim((string)$message['text']['body']);
        }
        if($type === 'button' && !empty($message['button']['text']))
        {
            return trim((string)$message['button']['text']);
        }
        if($type === 'interactive')
        {
            if(!empty($message['interactive']['button_reply']['title']))
            {
                return trim((string)$message['interactive']['button_reply']['title']);
            }
            if(!empty($message['interactive']['list_reply']['title']))
            {
                return trim((string)$message['interactive']['list_reply']['title']);
            }
        }
        if(($type === 'image' || $type === 'document' || $type === 'video') && !empty($message[$type]['caption']))
        {
            return trim((string)$message[$type]['caption']);
        }
        if($type !== '')
        {
            return '['.ucfirst($type).' message]';
        }
        return '[WhatsApp message]';
    }

    protected function _processFacebookLead($event, $settingsRow)
    {
        $event = (array)$event;
        $leadgenId = !empty($event['leadgen_id']) ? trim((string)$event['leadgen_id']) : '';
        if($leadgenId === '')
        {
            return false;
        }
        if($this->_alreadyProcessedExternalRef('facebook_leads', $leadgenId))
        {
            $this->_logEvent('facebook_leads', 'leadgen', $leadgenId, 'duplicate', 'Facebook lead already processed', $event);
            return true;
        }

        $fetch = $this->_facebookFetchLead($leadgenId, $settingsRow);
        if(empty($fetch['success']))
        {
            $this->_logEvent('facebook_leads', 'leadgen', $leadgenId, 'error', isset($fetch['message']) ? $fetch['message'] : 'Unable to fetch Facebook lead', $event);
            return false;
        }

        $leadData = (array)$fetch['data'];
        $fieldMap = $this->_facebookFieldMap(isset($leadData['field_data']) ? $leadData['field_data'] : array());
        $leadName = $this->_facebookValueFromAliases($fieldMap, array('full_name', 'name', 'full name'));
        if($leadName === '')
        {
            $firstName = $this->_facebookValueFromAliases($fieldMap, array('first_name', 'first name'));
            $lastName = $this->_facebookValueFromAliases($fieldMap, array('last_name', 'last name'));
            $leadName = trim($firstName.' '.$lastName);
        }
        $leadMobile = $this->_facebookValueFromAliases($fieldMap, array('phone_number', 'phone', 'mobile_phone', 'mobile', 'phone number'));
        $leadEmail = $this->_facebookValueFromAliases($fieldMap, array('email', 'email_address'));
        $leadAddress = $this->_facebookValueFromAliases($fieldMap, array('street_address', 'address'));
        $city = $this->_facebookValueFromAliases($fieldMap, array('city'));
        $state = $this->_facebookValueFromAliases($fieldMap, array('state', 'province'));
        $country = $this->_facebookValueFromAliases($fieldMap, array('country'));
        $budget = $this->_facebookValueFromAliases($fieldMap, array('budget', 'price_range', 'budget_range'));
        $language = $this->_facebookValueFromAliases($fieldMap, array('language', 'preferred_language'));
        $addressParts = array_filter(array($leadAddress, $city, $state, $country));
        $leadAddress = implode(', ', $addressParts);

        $assignment = $this->_resolveAssignment('facebook_leads', $settingsRow, array(
            array('match_type' => 'form_id', 'external_id' => isset($leadData['form_id']) ? $leadData['form_id'] : ''),
            array('match_type' => 'page_id', 'external_id' => isset($event['page_id']) ? $event['page_id'] : '')
        ));
        if((int)$assignment['project_id'] <= 0 || (int)$assignment['user_id'] <= 0)
        {
            $this->_logEvent('facebook_leads', 'leadgen', $leadgenId, 'error', 'No valid Facebook assignment route/default found', $leadData, $assignment);
            return false;
        }

        $knownKeys = array('full_name', 'name', 'first_name', 'last_name', 'phone_number', 'phone', 'mobile_phone', 'mobile', 'phone number', 'email', 'email_address', 'street_address', 'address', 'city', 'state', 'province', 'country');
        $remarksParts = array();
        foreach($fieldMap as $fieldName => $fieldValue)
        {
            if($fieldValue === '' || in_array($fieldName, $knownKeys, true))
            {
                continue;
            }
            $remarksParts[] = $fieldName.': '.$fieldValue;
        }
        if(!empty($leadData['ad_id']))
        {
            $remarksParts[] = 'ad_id: '.trim((string)$leadData['ad_id']);
        }
        if(!empty($leadData['form_id']))
        {
            $remarksParts[] = 'form_id: '.trim((string)$leadData['form_id']);
        }
        $createdTime = !empty($leadData['created_time']) ? trim((string)$leadData['created_time']) : $this->currentDateTime;
        $payload = $this->_buildLeadPayload(array(
            'lead_name' => $leadName,
            'lead_mobile' => $leadMobile,
            'lead_email' => $leadEmail,
            'lead_address' => $leadAddress,
            'lead_city' => $city,
            'lead_budget' => $budget,
            'lead_language' => $language,
            'lead_source' => $assignment['lead_source'],
            'source_form_id' => isset($leadData['form_id']) ? $leadData['form_id'] : '',
            'source_ad_id' => isset($leadData['ad_id']) ? $leadData['ad_id'] : '',
            'status_id' => $assignment['status_id'],
            'last_contact_date' => $createdTime,
            'follow_up' => $createdTime,
            'remarks' => implode("\n", $remarksParts)
        ), $assignment);
        $automationResult = $this->_applyCrmAutomationToLeadPayload($payload, 'webhook', 0);
        $payload = isset($automationResult['payload']) ? (array)$automationResult['payload'] : $payload;
        if(trim((string)$payload['lead_name']) === '' && trim((string)$payload['lead_mobile']) === '' && trim((string)$payload['lead_email']) === '' && (int)$payload['client_id'] <= 0)
        {
            $this->_logEvent('facebook_leads', 'leadgen', $leadgenId, 'error', 'Facebook lead did not contain usable contact data', $leadData, $assignment);
            return false;
        }
        if(empty($automationResult['success']))
        {
            $this->_logEvent('facebook_leads', 'leadgen', $leadgenId, 'duplicate', !empty($automationResult['message']) ? $automationResult['message'] : 'Duplicate lead blocked by automation settings', $leadData, $assignment);
            return false;
        }
        $this->Lead->create();
        if($this->Lead->save(array('Lead' => $payload), array('validate' => false)))
        {
            $this->_logEvent('facebook_leads', 'leadgen', $leadgenId, 'saved', 'Facebook lead imported successfully', $leadData, $assignment, (int)$this->Lead->id);
            return true;
        }
        $errorText = trim((string)$this->Lead->getDataSource()->lastError());
        $this->_logEvent('facebook_leads', 'leadgen', $leadgenId, 'error', ($errorText !== '' ? $errorText : 'Unable to save Facebook lead'), $leadData, $assignment);
        return false;
    }

    protected function _processWhatsAppMessage($message, $value, $settingsRow)
    {
        $message = (array)$message;
        $value = (array)$value;
        $messageId = !empty($message['id']) ? trim((string)$message['id']) : '';
        if($messageId === '')
        {
            return false;
        }
        if($this->_alreadyProcessedExternalRef('whatsapp_cloud', $messageId))
        {
            $this->_logEvent('whatsapp_cloud', 'message', $messageId, 'duplicate', 'WhatsApp message already processed', $message);
            return true;
        }

        $metadata = !empty($value['metadata']) ? (array)$value['metadata'] : array();
        $contacts = !empty($value['contacts'][0]) ? (array)$value['contacts'][0] : array();
        $contactName = !empty($contacts['profile']['name']) ? trim((string)$contacts['profile']['name']) : '';
        $waId = !empty($contacts['wa_id']) ? trim((string)$contacts['wa_id']) : trim((string)(isset($message['from']) ? $message['from'] : ''));
        $messageText = $this->_extractWhatsAppMessageText($message);

        // Two-Way WhatsApp Bot for Registered Clients
        if (!empty($messageText) && !empty($waId)) {
            App::uses('WhatsAppAutomationComponent', 'Controller/Component');
            $waBot = new WhatsAppAutomationComponent(new ComponentCollection());
            if ($waBot->handleBotQuery($waId, $messageText)) {
                $this->_logEvent('whatsapp_cloud', 'bot_query', $messageId, 'bot_replied', 'Two-way bot auto-reply sent to client', array('from' => $waId, 'text' => $messageText));
                return true;
            }
        }

        $assignment = $this->_resolveAssignment('whatsapp_cloud', $settingsRow, array(
            array('match_type' => 'phone_number_id', 'external_id' => isset($metadata['phone_number_id']) ? $metadata['phone_number_id'] : '')
        ));
        if((int)$assignment['project_id'] <= 0 || (int)$assignment['user_id'] <= 0)
        {
            $this->_logEvent('whatsapp_cloud', 'message', $messageId, 'error', 'No valid WhatsApp assignment route/default found', $value, $assignment);
            return false;
        }

        $existingLead = $this->_findExistingLeadByContact($assignment['project_id'], $waId, '');
        $remarkText = '[WhatsApp '.date('Y-m-d H:i:s').'] '.$messageText;
        if(!empty($existingLead['Lead']['id']))
        {
            $updatePayload = array(
                'id' => (int)$existingLead['Lead']['id'],
                'user_id' => (int)$assignment['user_id'],
                'status_id' => (int)$assignment['status_id'],
                'last_contact_date' => $this->currentDate,
                'follow_up' => $this->currentDateTime,
                'remarks' => $this->_appendRemarkText(isset($existingLead['Lead']['remarks']) ? $existingLead['Lead']['remarks'] : '', $remarkText)
            );
            if(trim((string)(isset($existingLead['Lead']['lead_name']) ? $existingLead['Lead']['lead_name'] : '')) === '' && $contactName !== '')
            {
                $updatePayload['lead_name'] = $contactName;
            }
            if(trim((string)(isset($existingLead['Lead']['lead_source']) ? $existingLead['Lead']['lead_source'] : '')) === '')
            {
                $updatePayload['lead_source'] = $assignment['lead_source'];
            }
            if($this->Lead->save(array('Lead' => $updatePayload), array('validate' => false)))
            {
                $this->_logEvent('whatsapp_cloud', 'message', $messageId, 'updated_existing', 'Updated existing lead from WhatsApp message', $value, $assignment, (int)$existingLead['Lead']['id']);
                return true;
            }
        }

        $payload = $this->_buildLeadPayload(array(
            'lead_name' => ($contactName !== '' ? $contactName : $waId),
            'lead_mobile' => $waId,
            'lead_email' => '',
            'lead_address' => '',
            'lead_source' => $assignment['lead_source'],
            'status_id' => $assignment['status_id'],
            'last_contact_date' => $this->currentDate,
            'follow_up' => $this->currentDateTime,
            'remarks' => $remarkText
        ), $assignment);
        $automationResult = $this->_applyCrmAutomationToLeadPayload($payload, 'webhook', 0);
        $payload = isset($automationResult['payload']) ? (array)$automationResult['payload'] : $payload;
        if(empty($automationResult['success']))
        {
            $this->_logEvent('whatsapp_cloud', 'message', $messageId, 'duplicate', !empty($automationResult['message']) ? $automationResult['message'] : 'Duplicate lead blocked by automation settings', $value, $assignment);
            return false;
        }
        $this->Lead->create();
        if($this->Lead->save(array('Lead' => $payload), array('validate' => false)))
        {
            $this->_logEvent('whatsapp_cloud', 'message', $messageId, 'saved', 'WhatsApp lead imported successfully', $value, $assignment, (int)$this->Lead->id);
            return true;
        }
        $errorText = trim((string)$this->Lead->getDataSource()->lastError());
        $this->_logEvent('whatsapp_cloud', 'message', $messageId, 'error', ($errorText !== '' ? $errorText : 'Unable to save WhatsApp lead'), $value, $assignment);
        return false;
    }

    public function facebook()
    {
        $settingsRow = $this->_providerSettings('facebook_leads');
        $settings = !empty($settingsRow['CrmIntegration']) ? (array)$settingsRow['CrmIntegration'] : array();
        if($this->request->is('get'))
        {
            $mode = trim((string)$this->request->query('hub_mode'));
            if($mode === '')
            {
                $mode = trim((string)$this->request->query('hub.mode'));
            }
            $verifyToken = trim((string)$this->request->query('hub_verify_token'));
            if($verifyToken === '')
            {
                $verifyToken = trim((string)$this->request->query('hub.verify_token'));
            }
            $challenge = trim((string)$this->request->query('hub_challenge'));
            if($challenge === '')
            {
                $challenge = trim((string)$this->request->query('hub.challenge'));
            }
            $savedVerifyToken = trim((string)(isset($settings['verify_token']) ? $settings['verify_token'] : ''));
            if($mode === 'subscribe' && $savedVerifyToken !== '' && hash_equals($savedVerifyToken, $verifyToken))
            {
                return $this->_writePlainResponse($challenge, 200);
            }
            return $this->_writePlainResponse('Verification failed', 403);
        }

        $rawBody = file_get_contents('php://input');
        $payload = json_decode((string)$rawBody, true);
        if(empty($settings['enabled']))
        {
            return $this->_writeJsonResponse(array('success' => true, 'processed' => 0, 'message' => 'Facebook integration disabled'), 200);
        }
        if(!is_array($payload))
        {
            return $this->_writeJsonResponse(array('success' => false, 'message' => 'Invalid JSON payload'), 400);
        }

        $processed = 0;
        foreach((array)(isset($payload['entry']) ? $payload['entry'] : array()) as $entry)
        {
            $changes = isset($entry['changes']) ? (array)$entry['changes'] : array();
            foreach($changes as $change)
            {
                if(empty($change['field']) || strtolower(trim((string)$change['field'])) !== 'leadgen')
                {
                    continue;
                }
                $value = !empty($change['value']) ? (array)$change['value'] : array();
                if($this->_processFacebookLead($value, $settingsRow))
                {
                    $processed++;
                }
            }
        }
        return $this->_writeJsonResponse(array('success' => true, 'processed' => $processed), 200);
    }

    public function whatsapp()
    {
        $settingsRow = $this->_providerSettings('whatsapp_cloud');
        $settings = !empty($settingsRow['CrmIntegration']) ? (array)$settingsRow['CrmIntegration'] : array();
        if($this->request->is('get'))
        {
            $mode = trim((string)$this->request->query('hub_mode'));
            if($mode === '')
            {
                $mode = trim((string)$this->request->query('hub.mode'));
            }
            $verifyToken = trim((string)$this->request->query('hub_verify_token'));
            if($verifyToken === '')
            {
                $verifyToken = trim((string)$this->request->query('hub.verify_token'));
            }
            $challenge = trim((string)$this->request->query('hub_challenge'));
            if($challenge === '')
            {
                $challenge = trim((string)$this->request->query('hub.challenge'));
            }
            $savedVerifyToken = trim((string)(isset($settings['verify_token']) ? $settings['verify_token'] : ''));
            if($mode === 'subscribe' && $savedVerifyToken !== '' && hash_equals($savedVerifyToken, $verifyToken))
            {
                return $this->_writePlainResponse($challenge, 200);
            }
            return $this->_writePlainResponse('Verification failed', 403);
        }

        $rawBody = file_get_contents('php://input');
        $payload = json_decode((string)$rawBody, true);
        if(empty($settings['enabled']))
        {
            return $this->_writeJsonResponse(array('success' => true, 'processed' => 0, 'message' => 'WhatsApp integration disabled'), 200);
        }
        if(!is_array($payload))
        {
            return $this->_writeJsonResponse(array('success' => false, 'message' => 'Invalid JSON payload'), 400);
        }

        $processed = 0;
        foreach((array)(isset($payload['entry']) ? $payload['entry'] : array()) as $entry)
        {
            foreach((array)(isset($entry['changes']) ? $entry['changes'] : array()) as $change)
            {
                $value = !empty($change['value']) ? (array)$change['value'] : array();
                foreach((array)(isset($value['messages']) ? $value['messages'] : array()) as $message)
                {
                    if($this->_processWhatsAppMessage($message, $value, $settingsRow))
                    {
                        $processed++;
                    }
                }
            }
        }
        return $this->_writeJsonResponse(array('success' => true, 'processed' => $processed), 200);
    }

    public function whatsapp_bot()
    {
        return $this->whatsapp();
    }
}
?>

