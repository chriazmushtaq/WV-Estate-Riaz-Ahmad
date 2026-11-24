<?php
class CrmGoogleCalendar extends AppModel
{
    public $useTable = false;
    public $cacheSources = false;

    protected $_googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected $_googleTokenUrl = 'https://oauth2.googleapis.com/token';
    protected $_googleUserInfoUrl = 'https://openidconnect.googleapis.com/v1/userinfo';
    protected $_googleCalendarListUrl = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';

    public function ensureSchema()
    {
        static $isEnsured = null;
        if($isEnsured !== null)
        {
            return $isEnsured;
        }
        $isEnsured = false;
        try
        {
            $CrmIntegration = ClassRegistry::init('CrmIntegration');
            if($CrmIntegration)
            {
                $CrmIntegration->ensureSchema();
                $CrmIntegration->query(
                    "CREATE TABLE IF NOT EXISTS `crm_google_calendar_settings` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `client_id` varchar(255) DEFAULT NULL,
                        `client_secret` varchar(255) DEFAULT NULL,
                        `calendar_id` varchar(191) DEFAULT 'primary',
                        `access_token` longtext NULL,
                        `refresh_token` longtext NULL,
                        `token_expires_at` datetime DEFAULT NULL,
                        `google_email` varchar(191) DEFAULT NULL,
                        `google_account_id` varchar(191) DEFAULT NULL,
                        `is_connected` tinyint(1) NOT NULL DEFAULT 0,
                        `created` datetime DEFAULT NULL,
                        `modified` datetime DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
                );
                $CrmIntegration->query(
                    "CREATE TABLE IF NOT EXISTS `crm_user_google_calendar_settings` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `user_id` int(11) NOT NULL,
                        `use_global` tinyint(1) NOT NULL DEFAULT 1,
                        `client_id` varchar(255) DEFAULT NULL,
                        `client_secret` varchar(255) DEFAULT NULL,
                        `calendar_id` varchar(191) DEFAULT 'primary',
                        `access_token` longtext NULL,
                        `refresh_token` longtext NULL,
                        `token_expires_at` datetime DEFAULT NULL,
                        `google_email` varchar(191) DEFAULT NULL,
                        `google_account_id` varchar(191) DEFAULT NULL,
                        `sync_enabled` tinyint(1) NOT NULL DEFAULT 0,
                        `sync_follow_up` tinyint(1) NOT NULL DEFAULT 1,
                        `sync_calendar_tasks` tinyint(1) NOT NULL DEFAULT 1,
                        `last_sync_at` datetime DEFAULT NULL,
                        `is_connected` tinyint(1) NOT NULL DEFAULT 0,
                        `created` datetime DEFAULT NULL,
                        `modified` datetime DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `crm_user_google_calendar_settings_user_unique` (`user_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
                );
            }
            $this->_ensureLeadColumns();
            $this->_ensureCalendarColumns();
            $this->schema(true);
            $isEnsured = true;
        }
        catch(Exception $e)
        {
            $isEnsured = false;
        }
        return $isEnsured;
    }

    protected function _ensureLeadColumns()
    {
        $Lead = ClassRegistry::init('Lead');
        if(!$Lead)
        {
            return false;
        }
        $columns = array(
            'google_calendar_event_id' => "varchar(191) DEFAULT NULL",
            'google_calendar_synced_at' => "datetime DEFAULT NULL"
        );
        foreach($columns as $columnName => $columnSql)
        {
            try
            {
                $exists = $Lead->query("SHOW COLUMNS FROM `leads` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $Lead->query("ALTER TABLE `leads` ADD `{$columnName}` {$columnSql}");
                }
            }
            catch(Exception $e)
            {
            }
        }
        return true;
    }

    protected function _ensureCalendarColumns()
    {
        $Calendar = ClassRegistry::init('Calendar');
        if(!$Calendar)
        {
            return false;
        }
        $columns = array(
            'google_calendar_event_id' => "varchar(191) DEFAULT NULL",
            'google_calendar_synced_at' => "datetime DEFAULT NULL"
        );
        foreach($columns as $columnName => $columnSql)
        {
            try
            {
                $exists = $Calendar->query("SHOW COLUMNS FROM `calendars` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $Calendar->query("ALTER TABLE `calendars` ADD `{$columnName}` {$columnSql}");
                }
            }
            catch(Exception $e)
            {
            }
        }
        return true;
    }

    protected function _globalModel()
    {
        $this->ensureSchema();
        $Model = ClassRegistry::init('CrmGoogleCalendarSetting');
        if($Model)
        {
            $Model->cacheSources = false;
        }
        return $Model;
    }

    protected function _userModel()
    {
        $this->ensureSchema();
        $Model = ClassRegistry::init('CrmUserGoogleCalendarSetting');
        if($Model)
        {
            $Model->cacheSources = false;
        }
        return $Model;
    }

    public function defaultGlobalRow()
    {
        return array(
            'CrmGoogleCalendarSetting' => array(
                'id' => null,
                'client_id' => '',
                'client_secret' => '',
                'calendar_id' => 'primary',
                'access_token' => '',
                'refresh_token' => '',
                'token_expires_at' => null,
                'google_email' => '',
                'google_account_id' => '',
                'is_connected' => 0
            )
        );
    }

    public function findGlobalRow()
    {
        $defaults = $this->defaultGlobalRow();
        $Model = $this->_globalModel();
        if(!$Model)
        {
            return $defaults;
        }
        $row = (array)$Model->find('first', array(
            'order' => array('CrmGoogleCalendarSetting.id' => 'asc'),
            'recursive' => -1
        ));
        if(empty($row['CrmGoogleCalendarSetting']))
        {
            return $defaults;
        }
        $row['CrmGoogleCalendarSetting'] = array_merge((array)$defaults['CrmGoogleCalendarSetting'], (array)$row['CrmGoogleCalendarSetting']);
        if(trim((string)$row['CrmGoogleCalendarSetting']['calendar_id']) === '')
        {
            $row['CrmGoogleCalendarSetting']['calendar_id'] = 'primary';
        }
        return $row;
    }

    public function saveGlobalRow($data = array())
    {
        $Model = $this->_globalModel();
        if(!$Model)
        {
            return false;
        }
        $current = $this->findGlobalRow();
        $saveData = array_merge((array)$current['CrmGoogleCalendarSetting'], (array)$data);
        if(trim((string)(isset($saveData['calendar_id']) ? $saveData['calendar_id'] : '')) === '')
        {
            $saveData['calendar_id'] = 'primary';
        }
        $Model->create();
        if(!empty($current['CrmGoogleCalendarSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmGoogleCalendarSetting']['id'];
            $Model->id = (int)$current['CrmGoogleCalendarSetting']['id'];
        }
        return (bool)$Model->save(array('CrmGoogleCalendarSetting' => $saveData), array('validate' => false));
    }

    public function defaultUserRow($userId = 0)
    {
        return array(
            'CrmUserGoogleCalendarSetting' => array(
                'id' => null,
                'user_id' => (int)$userId,
                'use_global' => 1,
                'client_id' => '',
                'client_secret' => '',
                'calendar_id' => 'primary',
                'access_token' => '',
                'refresh_token' => '',
                'token_expires_at' => null,
                'google_email' => '',
                'google_account_id' => '',
                'sync_enabled' => 0,
                'sync_follow_up' => 1,
                'sync_calendar_tasks' => 1,
                'last_sync_at' => null,
                'is_connected' => 0
            )
        );
    }

    public function findUserRow($userId = 0)
    {
        $userId = (int)$userId;
        $defaults = $this->defaultUserRow($userId);
        if($userId <= 0)
        {
            return $defaults;
        }
        $Model = $this->_userModel();
        if(!$Model)
        {
            return $defaults;
        }
        $row = (array)$Model->find('first', array(
            'conditions' => array('CrmUserGoogleCalendarSetting.user_id' => $userId),
            'recursive' => -1
        ));
        if(empty($row['CrmUserGoogleCalendarSetting']))
        {
            return $defaults;
        }
        $row['CrmUserGoogleCalendarSetting'] = array_merge((array)$defaults['CrmUserGoogleCalendarSetting'], (array)$row['CrmUserGoogleCalendarSetting']);
        if(trim((string)$row['CrmUserGoogleCalendarSetting']['calendar_id']) === '')
        {
            $row['CrmUserGoogleCalendarSetting']['calendar_id'] = 'primary';
        }
        return $row;
    }

    public function saveUserRow($userId = 0, $data = array())
    {
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return false;
        }
        $Model = $this->_userModel();
        if(!$Model)
        {
            return false;
        }
        $current = $this->findUserRow($userId);
        $saveData = array_merge((array)$current['CrmUserGoogleCalendarSetting'], (array)$data);
        $saveData['user_id'] = $userId;
        $saveData['use_global'] = !empty($saveData['use_global']) ? 1 : 0;
        $saveData['sync_enabled'] = !empty($saveData['sync_enabled']) ? 1 : 0;
        $saveData['sync_follow_up'] = !empty($saveData['sync_follow_up']) ? 1 : 0;
        $saveData['sync_calendar_tasks'] = !empty($saveData['sync_calendar_tasks']) ? 1 : 0;
        if(trim((string)(isset($saveData['calendar_id']) ? $saveData['calendar_id'] : '')) === '')
        {
            $saveData['calendar_id'] = 'primary';
        }
        $Model->create();
        if(!empty($current['CrmUserGoogleCalendarSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmUserGoogleCalendarSetting']['id'];
            $Model->id = (int)$current['CrmUserGoogleCalendarSetting']['id'];
        }
        return (bool)$Model->save(array('CrmUserGoogleCalendarSetting' => $saveData), array('validate' => false));
    }
    protected function _scopeRow($scopeType = 'user', $ownerId = 0)
    {
        $scopeType = strtolower(trim((string)$scopeType));
        if($scopeType === 'global')
        {
            return $this->findGlobalRow();
        }
        return $this->findUserRow((int)$ownerId);
    }

    protected function _scopeSettings($scopeType = 'user', $ownerId = 0)
    {
        $row = $this->_scopeRow($scopeType, $ownerId);
        if($scopeType === 'global')
        {
            return !empty($row['CrmGoogleCalendarSetting']) ? (array)$row['CrmGoogleCalendarSetting'] : array();
        }
        return !empty($row['CrmUserGoogleCalendarSetting']) ? (array)$row['CrmUserGoogleCalendarSetting'] : array();
    }

    public function effectiveSettingsForUser($userId = 0)
    {
        $userId = (int)$userId;
        $userRow = $this->findUserRow($userId);
        $globalRow = $this->findGlobalRow();
        $userSettings = !empty($userRow['CrmUserGoogleCalendarSetting']) ? (array)$userRow['CrmUserGoogleCalendarSetting'] : array();
        $globalSettings = !empty($globalRow['CrmGoogleCalendarSetting']) ? (array)$globalRow['CrmGoogleCalendarSetting'] : array();
        $useGlobal = empty($userSettings) || !empty($userSettings['use_global']);

        if($useGlobal)
        {
            $effective = array_merge((array)$globalSettings, array(
                'sync_enabled' => !empty($userSettings['sync_enabled']) ? 1 : 0,
                'sync_follow_up' => !empty($userSettings['sync_follow_up']) ? 1 : 0,
                'sync_calendar_tasks' => !empty($userSettings['sync_calendar_tasks']) ? 1 : 0
            ));
            if(trim((string)(isset($effective['calendar_id']) ? $effective['calendar_id'] : '')) === '')
            {
                $effective['calendar_id'] = 'primary';
            }
            return array(
                'use_global' => 1,
                'scope_type' => 'global',
                'scope_owner_id' => 0,
                'settings' => $effective,
                'user_settings' => $userSettings,
                'global_settings' => $globalSettings
            );
        }

        if(trim((string)(isset($userSettings['calendar_id']) ? $userSettings['calendar_id'] : '')) === '')
        {
            $userSettings['calendar_id'] = 'primary';
        }
        return array(
            'use_global' => 0,
            'scope_type' => 'user',
            'scope_owner_id' => $userId,
            'settings' => $userSettings,
            'user_settings' => $userSettings,
            'global_settings' => $globalSettings
        );
    }

    public function settingsLookConfigured($row = array())
    {
        $row = (array)$row;
        return (
            trim((string)(isset($row['client_id']) ? $row['client_id'] : '')) !== '' &&
            trim((string)(isset($row['client_secret']) ? $row['client_secret'] : '')) !== ''
        );
    }

    public function settingsConnected($row = array())
    {
        $row = (array)$row;
        return (!empty($row['is_connected']) && (trim((string)(isset($row['refresh_token']) ? $row['refresh_token'] : '')) !== '' || trim((string)(isset($row['access_token']) ? $row['access_token'] : '')) !== ''));
    }

    public function googleScopes()
    {
        return array(
            'openid',
            'email',
            'profile',
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events'
        );
    }

    public function buildAuthUrl($row = array(), $redirectUri = '', $state = '')
    {
        $row = (array)$row;
        if(!$this->settingsLookConfigured($row))
        {
            throw new Exception('Please save Google Client ID and Client Secret first.');
        }
        $params = array(
            'client_id' => isset($row['client_id']) ? (string)$row['client_id'] : '',
            'redirect_uri' => (string)$redirectUri,
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'scope' => implode(' ', $this->googleScopes()),
            'state' => (string)$state
        );
        return $this->_googleAuthUrl.'?'.http_build_query($params, '', '&');
    }

    public function exchangeCode($scopeType = 'user', $ownerId = 0, $code = '', $redirectUri = '')
    {
        $code = trim((string)$code);
        if($code === '')
        {
            throw new Exception('Missing Google authorization code.');
        }
        $scopeSettings = $this->_scopeSettings($scopeType, $ownerId);
        if(!$this->settingsLookConfigured($scopeSettings))
        {
            throw new Exception('Google Calendar credentials are not configured yet.');
        }

        $response = $this->_request('POST', $this->_googleTokenUrl, array(
            'code' => $code,
            'client_id' => isset($scopeSettings['client_id']) ? $scopeSettings['client_id'] : '',
            'client_secret' => isset($scopeSettings['client_secret']) ? $scopeSettings['client_secret'] : '',
            'redirect_uri' => (string)$redirectUri,
            'grant_type' => 'authorization_code'
        ), array(), false);

        if((int)$response['status'] < 200 || (int)$response['status'] >= 300)
        {
            throw new Exception($this->_extractGoogleErrorMessage($response, 'Unable to connect Google Calendar account.'));
        }

        $tokenPayload = $this->_tokenPayloadFromResponse((array)$response['json'], $scopeSettings);
        $userInfo = $this->_fetchGoogleUserInfoByToken(isset($tokenPayload['access_token']) ? $tokenPayload['access_token'] : '');
        if(!empty($userInfo['email']))
        {
            $tokenPayload['google_email'] = (string)$userInfo['email'];
        }
        if(!empty($userInfo['sub']))
        {
            $tokenPayload['google_account_id'] = (string)$userInfo['sub'];
        }
        $tokenPayload['is_connected'] = 1;

        $this->_saveScopeRow($scopeType, $ownerId, $tokenPayload);
        return $this->_scopeSettings($scopeType, $ownerId);
    }

    public function disconnect($scopeType = 'user', $ownerId = 0)
    {
        $saveData = array(
            'access_token' => '',
            'refresh_token' => '',
            'token_expires_at' => null,
            'google_email' => '',
            'google_account_id' => '',
            'is_connected' => 0
        );
        return $this->_saveScopeRow($scopeType, $ownerId, $saveData);
    }

    public function fetchCalendarList($scopeType = 'user', $ownerId = 0)
    {
        $response = $this->_authorizedRequest($scopeType, $ownerId, 'GET', $this->_googleCalendarListUrl);
        if((int)$response['status'] < 200 || (int)$response['status'] >= 300)
        {
            throw new Exception($this->_extractGoogleErrorMessage($response, 'Unable to fetch Google Calendar list.'));
        }
        $items = array();
        if(!empty($response['json']['items']) && is_array($response['json']['items']))
        {
            foreach($response['json']['items'] as $item)
            {
                $items[] = array(
                    'id' => isset($item['id']) ? (string)$item['id'] : '',
                    'summary' => isset($item['summary']) ? (string)$item['summary'] : '',
                    'primary' => !empty($item['primary']) ? 1 : 0
                );
            }
        }
        return $items;
    }

    protected function _saveScopeRow($scopeType = 'user', $ownerId = 0, $saveData = array())
    {
        $scopeType = strtolower(trim((string)$scopeType));
        if($scopeType === 'global')
        {
            return $this->saveGlobalRow((array)$saveData);
        }
        return $this->saveUserRow((int)$ownerId, (array)$saveData);
    }

    protected function _tokenPayloadFromResponse($responseJson = array(), $currentSettings = array())
    {
        $responseJson = (array)$responseJson;
        $currentSettings = (array)$currentSettings;
        $expiresIn = isset($responseJson['expires_in']) ? (int)$responseJson['expires_in'] : 3600;
        if($expiresIn <= 0)
        {
            $expiresIn = 3600;
        }
        return array(
            'access_token' => isset($responseJson['access_token']) ? (string)$responseJson['access_token'] : (isset($currentSettings['access_token']) ? (string)$currentSettings['access_token'] : ''),
            'refresh_token' => isset($responseJson['refresh_token']) ? (string)$responseJson['refresh_token'] : (isset($currentSettings['refresh_token']) ? (string)$currentSettings['refresh_token'] : ''),
            'token_expires_at' => date('Y-m-d H:i:s', time() + $expiresIn),
            'is_connected' => !empty($responseJson['access_token']) ? 1 : (!empty($currentSettings['is_connected']) ? 1 : 0)
        );
    }

    protected function _extractGoogleErrorMessage($response = array(), $fallback = 'Google request failed.')
    {
        if(!empty($response['json']['error_description']))
        {
            return (string)$response['json']['error_description'];
        }
        if(!empty($response['json']['error']['message']))
        {
            return (string)$response['json']['error']['message'];
        }
        if(!empty($response['body']))
        {
            return trim((string)$response['body']);
        }
        return (string)$fallback;
    }

    protected function _fetchGoogleUserInfoByToken($accessToken = '')
    {
        $accessToken = trim((string)$accessToken);
        if($accessToken === '')
        {
            return array();
        }
        $response = $this->_request('GET', $this->_googleUserInfoUrl, null, array('Authorization: Bearer '.$accessToken));
        if((int)$response['status'] < 200 || (int)$response['status'] >= 300)
        {
            return array();
        }
        return !empty($response['json']) ? (array)$response['json'] : array();
    }
    protected function _request($method = 'GET', $url = '', $payload = null, $headers = array(), $jsonPayload = true)
    {
        $method = strtoupper(trim((string)$method));
        $url = trim((string)$url);
        if($url === '')
        {
            throw new Exception('Missing request URL.');
        }
        if(!function_exists('curl_init'))
        {
            throw new Exception('PHP cURL extension is required for Google Calendar integration.');
        }
        $ch = curl_init();
        $requestHeaders = array('Accept: application/json');
        foreach((array)$headers as $headerValue)
        {
            if(trim((string)$headerValue) !== '')
            {
                $requestHeaders[] = (string)$headerValue;
            }
        }
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_HEADER => false
        );
        if($payload !== null)
        {
            if($jsonPayload)
            {
                $options[CURLOPT_POSTFIELDS] = json_encode($payload);
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            }
            else
            {
                $options[CURLOPT_POSTFIELDS] = is_array($payload) ? http_build_query($payload, '', '&') : (string)$payload;
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
            }
        }
        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        if($body === false)
        {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception(trim((string)$error) !== '' ? trim((string)$error) : 'Google request failed.');
        }
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = array();
        $decoded = json_decode((string)$body, true);
        if(is_array($decoded))
        {
            $json = $decoded;
        }
        return array(
            'status' => $status,
            'body' => (string)$body,
            'json' => $json
        );
    }

    protected function _authorizedRequest($scopeType = 'user', $ownerId = 0, $method = 'GET', $url = '', $payload = null, $jsonPayload = true)
    {
        $token = $this->_validAccessToken($scopeType, $ownerId);
        $response = $this->_request($method, $url, $payload, array('Authorization: Bearer '.$token), $jsonPayload);
        if(in_array((int)$response['status'], array(401, 403), true))
        {
            $scopeSettings = $this->_scopeSettings($scopeType, $ownerId);
            if(trim((string)(isset($scopeSettings['refresh_token']) ? $scopeSettings['refresh_token'] : '')) !== '')
            {
                $this->refreshAccessToken($scopeType, $ownerId);
                $token = $this->_validAccessToken($scopeType, $ownerId);
                $response = $this->_request($method, $url, $payload, array('Authorization: Bearer '.$token), $jsonPayload);
            }
        }
        return $response;
    }

    protected function _validAccessToken($scopeType = 'user', $ownerId = 0)
    {
        $scopeSettings = $this->_scopeSettings($scopeType, $ownerId);
        if(!$this->settingsLookConfigured($scopeSettings))
        {
            throw new Exception('Google Calendar credentials are not configured yet.');
        }
        $accessToken = trim((string)(isset($scopeSettings['access_token']) ? $scopeSettings['access_token'] : ''));
        $expiresAt = trim((string)(isset($scopeSettings['token_expires_at']) ? $scopeSettings['token_expires_at'] : ''));
        $refreshToken = trim((string)(isset($scopeSettings['refresh_token']) ? $scopeSettings['refresh_token'] : ''));
        $needsRefresh = false;
        if($accessToken === '')
        {
            $needsRefresh = ($refreshToken !== '');
        }
        elseif($expiresAt !== '')
        {
            $expiryTimestamp = strtotime($expiresAt);
            if($expiryTimestamp !== false && $expiryTimestamp <= (time() + 120) && $refreshToken !== '')
            {
                $needsRefresh = true;
            }
        }
        if($needsRefresh)
        {
            $scopeSettings = $this->refreshAccessToken($scopeType, $ownerId);
            $accessToken = trim((string)(isset($scopeSettings['access_token']) ? $scopeSettings['access_token'] : ''));
        }
        if($accessToken === '')
        {
            throw new Exception('Google Calendar account is not connected yet.');
        }
        return $accessToken;
    }

    public function refreshAccessToken($scopeType = 'user', $ownerId = 0)
    {
        $scopeSettings = $this->_scopeSettings($scopeType, $ownerId);
        if(!$this->settingsLookConfigured($scopeSettings))
        {
            throw new Exception('Google Calendar credentials are not configured yet.');
        }
        $refreshToken = trim((string)(isset($scopeSettings['refresh_token']) ? $scopeSettings['refresh_token'] : ''));
        if($refreshToken === '')
        {
            throw new Exception('Google Calendar refresh token is missing. Please reconnect the account.');
        }
        $response = $this->_request('POST', $this->_googleTokenUrl, array(
            'client_id' => isset($scopeSettings['client_id']) ? $scopeSettings['client_id'] : '',
            'client_secret' => isset($scopeSettings['client_secret']) ? $scopeSettings['client_secret'] : '',
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ), array(), false);
        if((int)$response['status'] < 200 || (int)$response['status'] >= 300)
        {
            throw new Exception($this->_extractGoogleErrorMessage($response, 'Unable to refresh Google Calendar token.'));
        }
        $saveData = $this->_tokenPayloadFromResponse((array)$response['json'], $scopeSettings);
        $saveData['refresh_token'] = $refreshToken;
        $saveData['is_connected'] = 1;
        $this->_saveScopeRow($scopeType, $ownerId, $saveData);
        return $this->_scopeSettings($scopeType, $ownerId);
    }

    protected function _googleEventEndpoint($calendarId = '', $eventId = '')
    {
        $calendarId = trim((string)$calendarId);
        if($calendarId === '')
        {
            $calendarId = 'primary';
        }
        $url = 'https://www.googleapis.com/calendar/v3/calendars/'.rawurlencode($calendarId).'/events';
        if(trim((string)$eventId) !== '')
        {
            $url .= '/'.rawurlencode((string)$eventId);
        }
        return $url;
    }

    protected function _googleEventDateTime($rawValue)
    {
        $rawValue = trim((string)$rawValue);
        if($rawValue === '')
        {
            return array();
        }
        try
        {
            $Date = new DateTime($rawValue);
        }
        catch(Exception $e)
        {
            return array();
        }
        return array(
            'dateTime' => $Date->format('c'),
            'timeZone' => date_default_timezone_get()
        );
    }

    protected function _leadSummaryText($leadRow = array())
    {
        $leadName = trim((string)(isset($leadRow['Lead']['lead_name']) ? $leadRow['Lead']['lead_name'] : ''));
        if($leadName === '' && !empty($leadRow['Client']['name']))
        {
            $leadName = trim((string)$leadRow['Client']['name']);
        }
        if($leadName === '')
        {
            $leadName = 'Lead #'.(int)(isset($leadRow['Lead']['id']) ? $leadRow['Lead']['id'] : 0);
        }
        return $leadName;
    }

    protected function _leadDescriptionText($leadRow = array())
    {
        $lines = array();
        $lines[] = 'Lead Name: '.$this->_leadSummaryText($leadRow);
        if(!empty($leadRow['Lead']['lead_mobile']))
        {
            $lines[] = 'Mobile: '.trim((string)$leadRow['Lead']['lead_mobile']);
        }
        if(!empty($leadRow['Lead']['lead_email']))
        {
            $lines[] = 'Email: '.trim((string)$leadRow['Lead']['lead_email']);
        }
        if(!empty($leadRow['Lead']['lead_source']))
        {
            $lines[] = 'Source: '.trim((string)$leadRow['Lead']['lead_source']);
        }
        if(!empty($leadRow['Status']['name']))
        {
            $lines[] = 'Status: '.trim((string)$leadRow['Status']['name']);
        }
        if(!empty($leadRow['Lead']['last_contact_date']) && $leadRow['Lead']['last_contact_date'] !== '0000-00-00')
        {
            $lines[] = 'Last Contact: '.date('d-M-Y', strtotime($leadRow['Lead']['last_contact_date']));
        }
        if(!empty($leadRow['Lead']['remarks']))
        {
            $lines[] = 'Remarks: '.trim((string)$leadRow['Lead']['remarks']);
        }
        return implode("\n", $lines);
    }

    protected function _calendarDescriptionText($calendarRow = array())
    {
        $lines = array();
        if(!empty($calendarRow['Calendar']['message_type']))
        {
            $lines[] = 'Task Type: '.trim((string)$calendarRow['Calendar']['message_type']);
        }
        if(!empty($calendarRow['Calendar']['comments']))
        {
            $lines[] = 'Comments: '.trim((string)$calendarRow['Calendar']['comments']);
        }
        if(!empty($calendarRow['User']['name']))
        {
            $lines[] = 'Assigned User: '.trim((string)$calendarRow['User']['name']);
        }
        if(!empty($calendarRow['Calendar']['color']))
        {
            $lines[] = 'Color: '.trim((string)$calendarRow['Calendar']['color']);
        }
        return implode("\n", $lines);
    }

    protected function _upsertEvent($scopeType = 'user', $ownerId = 0, $calendarId = '', $eventId = '', $eventData = array())
    {
        $calendarId = trim((string)$calendarId) !== '' ? trim((string)$calendarId) : 'primary';
        $eventId = trim((string)$eventId);
        if($eventId !== '')
        {
            $response = $this->_authorizedRequest($scopeType, $ownerId, 'PUT', $this->_googleEventEndpoint($calendarId, $eventId), $eventData, true);
            if((int)$response['status'] >= 200 && (int)$response['status'] < 300)
            {
                return !empty($response['json']) ? (array)$response['json'] : array();
            }
            if((int)$response['status'] !== 404)
            {
                throw new Exception($this->_extractGoogleErrorMessage($response, 'Unable to update Google Calendar event.'));
            }
        }
        $response = $this->_authorizedRequest($scopeType, $ownerId, 'POST', $this->_googleEventEndpoint($calendarId), $eventData, true);
        if((int)$response['status'] < 200 || (int)$response['status'] >= 300)
        {
            throw new Exception($this->_extractGoogleErrorMessage($response, 'Unable to create Google Calendar event.'));
        }
        return !empty($response['json']) ? (array)$response['json'] : array();
    }

    protected function _deleteEvent($scopeType = 'user', $ownerId = 0, $calendarId = '', $eventId = '')
    {
        $eventId = trim((string)$eventId);
        if($eventId === '')
        {
            return true;
        }
        $response = $this->_authorizedRequest($scopeType, $ownerId, 'DELETE', $this->_googleEventEndpoint($calendarId, $eventId));
        if(in_array((int)$response['status'], array(200, 204, 404), true))
        {
            return true;
        }
        throw new Exception($this->_extractGoogleErrorMessage($response, 'Unable to delete Google Calendar event.'));
    }

    protected function _touchUserSyncTime($userId = 0)
    {
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return false;
        }
        return $this->saveUserRow($userId, array('last_sync_at' => date('Y-m-d H:i:s')));
    }
    public function syncLeadFollowUpEventByLeadId($leadId = 0)
    {
        $leadId = (int)$leadId;
        if($leadId <= 0)
        {
            return array('success' => false, 'message' => 'Invalid lead.');
        }
        $Lead = ClassRegistry::init('Lead');
        if(!$Lead)
        {
            return array('success' => false, 'message' => 'Lead model unavailable.');
        }
        $leadRow = (array)$Lead->find('first', array(
            'conditions' => array('Lead.id' => $leadId),
            'recursive' => 0
        ));
        if(empty($leadRow['Lead']['id']))
        {
            return array('success' => false, 'message' => 'Lead not found.');
        }
        $userId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
        if($userId <= 0)
        {
            return array('success' => false, 'message' => 'Lead user is missing.');
        }
        $effective = $this->effectiveSettingsForUser($userId);
        $settings = !empty($effective['settings']) ? (array)$effective['settings'] : array();
        $calendarId = trim((string)(isset($settings['calendar_id']) ? $settings['calendar_id'] : 'primary'));
        $existingEventId = trim((string)(isset($leadRow['Lead']['google_calendar_event_id']) ? $leadRow['Lead']['google_calendar_event_id'] : ''));
        $followUp = trim((string)(isset($leadRow['Lead']['follow_up']) ? $leadRow['Lead']['follow_up'] : ''));
        $syncEnabled = !empty($settings['sync_enabled']) && !empty($settings['sync_follow_up']);

        if(!$syncEnabled || $followUp === '' || $followUp === '0000-00-00 00:00:00')
        {
            if($existingEventId !== '' && $this->settingsConnected($settings))
            {
                try
                {
                    $this->_deleteEvent($effective['scope_type'], (int)$effective['scope_owner_id'], $calendarId, $existingEventId);
                }
                catch(Exception $e)
                {
                }
            }
            $Lead->save(array('Lead' => array(
                'id' => $leadId,
                'google_calendar_event_id' => null,
                'google_calendar_synced_at' => null
            )), array('validate' => false));
            $this->_touchUserSyncTime($userId);
            return array('success' => true, 'message' => 'Lead follow-up sync skipped.', 'action' => 'cleared');
        }
        if(!$this->settingsLookConfigured($settings) || !$this->settingsConnected($settings))
        {
            return array('success' => false, 'message' => 'Google Calendar is not connected for this user.');
        }

        $eventData = array(
            'summary' => 'Lead Follow-up: '.$this->_leadSummaryText($leadRow),
            'description' => $this->_leadDescriptionText($leadRow),
            'start' => $this->_googleEventDateTime($followUp),
            'end' => $this->_googleEventDateTime(date('Y-m-d H:i:s', strtotime($followUp.' +30 minutes'))),
            'extendedProperties' => array(
                'private' => array(
                    'record_type' => 'lead_follow_up',
                    'record_id' => (string)$leadId,
                    'crm_source' => 'whitevillas'
                )
            ),
            'reminders' => array('useDefault' => true)
        );

        try
        {
            $eventRow = $this->_upsertEvent($effective['scope_type'], (int)$effective['scope_owner_id'], $calendarId, $existingEventId, $eventData);
            $savedEventId = !empty($eventRow['id']) ? (string)$eventRow['id'] : $existingEventId;
            $Lead->save(array('Lead' => array(
                'id' => $leadId,
                'google_calendar_event_id' => ($savedEventId !== '' ? $savedEventId : null),
                'google_calendar_synced_at' => date('Y-m-d H:i:s')
            )), array('validate' => false));
            $this->_touchUserSyncTime($userId);
            return array('success' => true, 'message' => 'Lead follow-up synced to Google Calendar.', 'event_id' => $savedEventId);
        }
        catch(Exception $e)
        {
            return array('success' => false, 'message' => trim((string)$e->getMessage()) !== '' ? trim((string)$e->getMessage()) : 'Unable to sync lead follow-up to Google Calendar.');
        }
    }

    public function removeLeadFollowUpEventByLeadId($leadId = 0)
    {
        $leadId = (int)$leadId;
        if($leadId <= 0)
        {
            return false;
        }
        $Lead = ClassRegistry::init('Lead');
        if(!$Lead)
        {
            return false;
        }
        $leadRow = (array)$Lead->find('first', array(
            'conditions' => array('Lead.id' => $leadId),
            'recursive' => -1
        ));
        if(empty($leadRow['Lead']['id']))
        {
            return false;
        }
        $existingEventId = trim((string)(isset($leadRow['Lead']['google_calendar_event_id']) ? $leadRow['Lead']['google_calendar_event_id'] : ''));
        if($existingEventId === '')
        {
            return true;
        }
        $userId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
        $effective = $this->effectiveSettingsForUser($userId);
        $settings = !empty($effective['settings']) ? (array)$effective['settings'] : array();
        if($this->settingsConnected($settings))
        {
            try
            {
                $this->_deleteEvent($effective['scope_type'], (int)$effective['scope_owner_id'], isset($settings['calendar_id']) ? $settings['calendar_id'] : 'primary', $existingEventId);
            }
            catch(Exception $e)
            {
            }
        }
        $Lead->save(array('Lead' => array(
            'id' => $leadId,
            'google_calendar_event_id' => null,
            'google_calendar_synced_at' => null
        )), array('validate' => false));
        return true;
    }

    public function syncCalendarTaskEventById($calendarId = 0)
    {
        $calendarId = (int)$calendarId;
        if($calendarId <= 0)
        {
            return array('success' => false, 'message' => 'Invalid calendar record.');
        }
        $Calendar = ClassRegistry::init('Calendar');
        if(!$Calendar)
        {
            return array('success' => false, 'message' => 'Calendar model unavailable.');
        }
        $calendarRow = (array)$Calendar->find('first', array(
            'conditions' => array('Calendar.id' => $calendarId),
            'recursive' => 0
        ));
        if(empty($calendarRow['Calendar']['id']))
        {
            return array('success' => false, 'message' => 'Calendar task not found.');
        }
        $userId = !empty($calendarRow['Calendar']['user_id']) ? (int)$calendarRow['Calendar']['user_id'] : 0;
        if($userId <= 0)
        {
            return array('success' => false, 'message' => 'Calendar user is missing.');
        }
        $effective = $this->effectiveSettingsForUser($userId);
        $settings = !empty($effective['settings']) ? (array)$effective['settings'] : array();
        $recordStart = trim((string)(isset($calendarRow['Calendar']['start_date']) ? $calendarRow['Calendar']['start_date'] : ''));
        $recordEnd = trim((string)(isset($calendarRow['Calendar']['end_date']) ? $calendarRow['Calendar']['end_date'] : ''));
        $existingEventId = trim((string)(isset($calendarRow['Calendar']['google_calendar_event_id']) ? $calendarRow['Calendar']['google_calendar_event_id'] : ''));
        $syncEnabled = !empty($settings['sync_enabled']) && !empty($settings['sync_calendar_tasks']);
        $selectedCalendarId = trim((string)(isset($settings['calendar_id']) ? $settings['calendar_id'] : 'primary'));

        if(!$syncEnabled || $recordStart === '')
        {
            if($existingEventId !== '' && $this->settingsConnected($settings))
            {
                try
                {
                    $this->_deleteEvent($effective['scope_type'], (int)$effective['scope_owner_id'], $selectedCalendarId, $existingEventId);
                }
                catch(Exception $e)
                {
                }
            }
            $Calendar->save(array('Calendar' => array(
                'id' => $calendarId,
                'google_calendar_event_id' => null,
                'google_calendar_synced_at' => null
            )), array('validate' => false));
            $this->_touchUserSyncTime($userId);
            return array('success' => true, 'message' => 'Calendar task sync skipped.', 'action' => 'cleared');
        }
        if(!$this->settingsLookConfigured($settings) || !$this->settingsConnected($settings))
        {
            return array('success' => false, 'message' => 'Google Calendar is not connected for this user.');
        }

        if($recordEnd === '' || $recordEnd === '0000-00-00 00:00:00')
        {
            $recordEnd = date('Y-m-d H:i:s', strtotime($recordStart.' +30 minutes'));
        }

        $summary = trim((string)(isset($calendarRow['Calendar']['message_type']) ? $calendarRow['Calendar']['message_type'] : ''));
        if($summary === '')
        {
            $summary = 'CRM Task';
        }
        else
        {
            $summary = 'Task: '.$summary;
        }

        $eventData = array(
            'summary' => $summary,
            'description' => $this->_calendarDescriptionText($calendarRow),
            'start' => $this->_googleEventDateTime($recordStart),
            'end' => $this->_googleEventDateTime($recordEnd),
            'extendedProperties' => array(
                'private' => array(
                    'record_type' => 'calendar_task',
                    'record_id' => (string)$calendarId,
                    'crm_source' => 'whitevillas'
                )
            ),
            'reminders' => array('useDefault' => true)
        );

        try
        {
            $eventRow = $this->_upsertEvent($effective['scope_type'], (int)$effective['scope_owner_id'], $selectedCalendarId, $existingEventId, $eventData);
            $savedEventId = !empty($eventRow['id']) ? (string)$eventRow['id'] : $existingEventId;
            $Calendar->save(array('Calendar' => array(
                'id' => $calendarId,
                'google_calendar_event_id' => ($savedEventId !== '' ? $savedEventId : null),
                'google_calendar_synced_at' => date('Y-m-d H:i:s')
            )), array('validate' => false));
            $this->_touchUserSyncTime($userId);
            return array('success' => true, 'message' => 'Calendar task synced to Google Calendar.', 'event_id' => $savedEventId);
        }
        catch(Exception $e)
        {
            return array('success' => false, 'message' => trim((string)$e->getMessage()) !== '' ? trim((string)$e->getMessage()) : 'Unable to sync calendar task to Google Calendar.');
        }
    }
    public function removeCalendarTaskEventById($calendarId = 0)
    {
        $calendarId = (int)$calendarId;
        if($calendarId <= 0)
        {
            return false;
        }
        $Calendar = ClassRegistry::init('Calendar');
        if(!$Calendar)
        {
            return false;
        }
        $calendarRow = (array)$Calendar->find('first', array(
            'conditions' => array('Calendar.id' => $calendarId),
            'recursive' => -1
        ));
        if(empty($calendarRow['Calendar']['id']))
        {
            return false;
        }
        $existingEventId = trim((string)(isset($calendarRow['Calendar']['google_calendar_event_id']) ? $calendarRow['Calendar']['google_calendar_event_id'] : ''));
        if($existingEventId === '')
        {
            return true;
        }
        $userId = !empty($calendarRow['Calendar']['user_id']) ? (int)$calendarRow['Calendar']['user_id'] : 0;
        $effective = $this->effectiveSettingsForUser($userId);
        $settings = !empty($effective['settings']) ? (array)$effective['settings'] : array();
        if($this->settingsConnected($settings))
        {
            try
            {
                $this->_deleteEvent($effective['scope_type'], (int)$effective['scope_owner_id'], isset($settings['calendar_id']) ? $settings['calendar_id'] : 'primary', $existingEventId);
            }
            catch(Exception $e)
            {
            }
        }
        $Calendar->save(array('Calendar' => array(
            'id' => $calendarId,
            'google_calendar_event_id' => null,
            'google_calendar_synced_at' => null
        )), array('validate' => false));
        return true;
    }

    public function syncUserUpcomingItems($userId = 0)
    {
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return array('success' => false, 'message' => 'Invalid user.', 'lead_count' => 0, 'calendar_count' => 0);
        }
        $Lead = ClassRegistry::init('Lead');
        $Calendar = ClassRegistry::init('Calendar');
        if(!$Lead || !$Calendar)
        {
            return array('success' => false, 'message' => 'Required models are unavailable.', 'lead_count' => 0, 'calendar_count' => 0);
        }
        $leadRows = (array)$Lead->find('all', array(
            'fields' => array('Lead.id'),
            'conditions' => array(
                'Lead.user_id' => $userId,
                'Lead.follow_up >=' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'Lead.follow_up !=' => '0000-00-00 00:00:00',
                'Lead.follow_up IS NOT NULL'
            ),
            'order' => array('Lead.follow_up' => 'asc'),
            'recursive' => -1
        ));
        $calendarRows = (array)$Calendar->find('all', array(
            'fields' => array('Calendar.id'),
            'conditions' => array(
                'Calendar.user_id' => $userId,
                'Calendar.start_date >=' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ),
            'order' => array('Calendar.start_date' => 'asc'),
            'recursive' => -1
        ));

        $leadCount = 0;
        foreach($leadRows as $row)
        {
            $result = $this->syncLeadFollowUpEventByLeadId(!empty($row['Lead']['id']) ? (int)$row['Lead']['id'] : 0);
            if(!empty($result['success']))
            {
                $leadCount++;
            }
        }

        $calendarCount = 0;
        foreach($calendarRows as $row)
        {
            $result = $this->syncCalendarTaskEventById(!empty($row['Calendar']['id']) ? (int)$row['Calendar']['id'] : 0);
            if(!empty($result['success']))
            {
                $calendarCount++;
            }
        }

        $this->_touchUserSyncTime($userId);
        return array(
            'success' => true,
            'message' => 'Google Calendar sync completed.',
            'lead_count' => $leadCount,
            'calendar_count' => $calendarCount
        );
    }
}
?>
