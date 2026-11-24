<?php
/**
 * LeadbotGatewayClient - Enterprise WhatsApp & SMS Gateway REST API Client
 *
 * Integrates White Villas with LeadBot CRM (F:\xampp\htdocs\whatsapp and live server leadbotcrm.com).
 * Supports WhatsApp multi-profile routing, Android SMS Gateway device & SIM selection,
 * real-time telemetry, resources monitoring, and automated event notifications.
 */

class LeadbotGatewayClient
{
    const DEFAULT_LOCAL_URL = 'http://localhost/whatsapp';
    const DEFAULT_LIVE_URL  = 'https://leadbotcrm.com';
    const TIMEOUT_SECONDS   = 15;

    /**
     * Normalize gateway base URL
     */
    public static function normalizeUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            $url = self::DEFAULT_LOCAL_URL;
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) ? ('http://' . $url) : ('https://' . $url);
        }
        return rtrim($url, '/');
    }

    /**
     * Clean and format phone number for international standards (Pakistan 923... or global)
     */
    public static function cleanPhone($phone)
    {
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);
        if (substr($clean, 0, 2) === '03' && strlen($clean) === 11) {
            $clean = '92' . substr($clean, 1);
        } elseif (substr($clean, 0, 1) === '3' && strlen($clean) === 10) {
            $clean = '92' . $clean;
        }
        return $clean;
    }

    /**
     * Replace template merge tags (supports both {tag} and {{tag}} syntax and dynamic keys)
     */
    public static function compileTemplate($template, array $data = array())
    {
        $base = array(
            'client_name'    => isset($data['client_name']) ? (string)$data['client_name'] : (isset($data['name']) ? (string)$data['name'] : 'Valued Client'),
            'name'           => isset($data['name']) ? (string)$data['name'] : (isset($data['client_name']) ? (string)$data['client_name'] : 'Valued Client'),
            'agent_name'     => isset($data['agent_name']) ? (string)$data['agent_name'] : (isset($data['user_name']) ? (string)$data['user_name'] : 'Property Consultant'),
            'phone'          => isset($data['phone']) ? (string)$data['phone'] : (isset($data['mobile']) ? (string)$data['mobile'] : ''),
            'mobile'         => isset($data['mobile']) ? (string)$data['mobile'] : (isset($data['phone']) ? (string)$data['phone'] : ''),
            'project_name'   => isset($data['project_name']) ? (string)$data['project_name'] : (isset($data['agency_name']) ? (string)$data['agency_name'] : 'White Villas'),
            'agency_name'    => isset($data['agency_name']) ? (string)$data['agency_name'] : (isset($data['project_name']) ? (string)$data['project_name'] : 'White Villas'),
            'site_name'      => isset($data['site_name']) ? (string)$data['site_name'] : (isset($data['agency_name']) ? (string)$data['agency_name'] : 'White Villas'),
            'agency_phone'   => isset($data['agency_phone']) ? (string)$data['agency_phone'] : '',
            'agency_email'   => isset($data['agency_email']) ? (string)$data['agency_email'] : '',
            'agent_mobile'   => isset($data['agent_mobile']) ? (string)$data['agent_mobile'] : '',
            'deal_title'     => isset($data['deal_title']) ? (string)$data['deal_title'] : (isset($data['title']) ? (string)$data['title'] : 'Property Booking'),
            'amount'         => isset($data['amount']) ? (string)$data['amount'] : '',
            'due_date'       => isset($data['due_date']) ? (string)$data['due_date'] : date('d M Y'),
            'remarks'        => isset($data['remarks']) ? (string)$data['remarks'] : (isset($data['message']) ? (string)$data['message'] : ''),
            'company_name'   => isset($data['company_name']) ? (string)$data['company_name'] : (isset($data['agency_name']) ? (string)$data['agency_name'] : 'White Villas'),
            'login_url'      => isset($data['login_url']) ? (string)$data['login_url'] : (isset($data['portal_url']) ? (string)$data['portal_url'] : ''),
            'portal_url'     => isset($data['portal_url']) ? (string)$data['portal_url'] : (isset($data['login_url']) ? (string)$data['login_url'] : ''),
            'email'          => isset($data['email']) ? (string)$data['email'] : '',
            'username'       => isset($data['username']) ? (string)$data['username'] : (isset($data['email']) ? (string)$data['email'] : ''),
            'password'       => isset($data['password']) ? (string)$data['password'] : (isset($data['plain_password']) ? (string)$data['plain_password'] : ''),
            'plain_password' => isset($data['plain_password']) ? (string)$data['plain_password'] : (isset($data['password']) ? (string)$data['password'] : ''),
            'date'           => date('d M Y'),
            'time'           => date('h:i A')
        );

        $merged = array_merge($base, $data);
        $replacements = array();
        foreach ($merged as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $str = (string)$v;
                $replacements['{' . $k . '}']   = $str;
                $replacements['{{' . $k . '}}'] = $str;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), (string)$template);
    }

    /**
     * Execute an HTTP cURL request to the LeadBot CRM gateway
     */
    public static function request($endpointUrl, $apiKey = '', array $payload = array(), $method = 'POST')
    {
        $startTime = microtime(true);
        $method = strtoupper(trim((string)$method));

        $ch = curl_init();
        $headers = array(
            'Accept: application/json',
            'User-Agent: WhiteVilas-CakePHP-Gateway/2.0'
        );

        if ($apiKey !== '') {
            $headers[] = 'X-API-KEY: ' . $apiKey;
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }

        if ($method === 'GET' && !empty($payload)) {
            $endpointUrl .= (strpos($endpointUrl, '?') === false ? '?' : '&') . http_build_query($payload);
        }

        curl_setopt($ch, CURLOPT_URL, $endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $rawResponse = curl_exec($ch);
        $httpCode    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        $latencyMs   = round((microtime(true) - $startTime) * 1000);
        curl_close($ch);

        if ($rawResponse === false || $curlError !== '') {
            return array(
                'success'     => false,
                'http_code'   => $httpCode,
                'latency_ms'  => $latencyMs,
                'error'       => 'Connection error: ' . ($curlError !== '' ? $curlError : 'Server unreachable'),
                'raw'         => null,
                'data'        => array()
            );
        }

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            // Check if server returned plain text or html error
            return array(
                'success'     => false,
                'http_code'   => $httpCode,
                'latency_ms'  => $latencyMs,
                'error'       => 'Invalid JSON response from gateway (HTTP ' . $httpCode . ')',
                'raw'         => $rawResponse,
                'data'        => array()
            );
        }

        $isSuccess = ($httpCode >= 200 && $httpCode < 300) && (!isset($decoded['success']) || !empty($decoded['success']));

        return array(
            'success'     => $isSuccess,
            'http_code'   => $httpCode,
            'latency_ms'  => $latencyMs,
            'error'       => !empty($decoded['error']) ? $decoded['error'] : ($isSuccess ? '' : 'Gateway returned error'),
            'raw'         => $rawResponse,
            'data'        => $decoded
        );
    }

    /**
     * Test connection to LeadBot CRM and check latency / health
     */
    public static function ping($gatewayUrl, $apiKey = '')
    {
        $baseUrl = self::normalizeUrl($gatewayUrl);
        $endpoint = $baseUrl . '/api/gateways.php';
        $res = self::request($endpoint, $apiKey, array('action' => 'telemetry'), 'GET');

        if ($res['success']) {
            return array(
                'connected'   => true,
                'latency_ms'  => $res['latency_ms'],
                'message'     => 'Connected to LeadBot CRM successfully (' . $res['latency_ms'] . ' ms)',
                'server_time' => isset($res['data']['server_time']) ? $res['data']['server_time'] : date('c'),
                'data'        => $res['data']
            );
        }

        return array(
            'connected'  => false,
            'latency_ms' => $res['latency_ms'],
            'message'    => !empty($res['error']) ? $res['error'] : 'Failed to connect to gateway',
            'http_code'  => $res['http_code']
        );
    }

    /**
     * Fetch all available WhatsApp profiles (sessions) and Android SMS devices for the agency
     */
    public static function getGateways($gatewayUrl, $apiKey = '', $agencyId = null)
    {
        $baseUrl = self::normalizeUrl($gatewayUrl);
        $endpoint = $baseUrl . '/api/gateways.php';
        $params = array('action' => 'gateways');
        if ($agencyId !== null && (int)$agencyId > 0) {
            $params['agency_id'] = (int)$agencyId;
        }

        $res = self::request($endpoint, $apiKey, $params, 'GET');
        if ($res['success']) {
            $waSessions = isset($res['data']['whatsapp_sessions']) && is_array($res['data']['whatsapp_sessions']) ? $res['data']['whatsapp_sessions'] : array();
            $smsDevices = isset($res['data']['sms_devices']) && is_array($res['data']['sms_devices']) ? $res['data']['sms_devices'] : array();

            return array(
                'success'           => true,
                'latency_ms'        => $res['latency_ms'],
                'whatsapp_sessions' => $waSessions,
                'sms_devices'       => $smsDevices,
                'total_sessions'    => count($waSessions),
                'total_devices'     => count($smsDevices),
                'server_time'       => isset($res['data']['server_time']) ? $res['data']['server_time'] : date('c')
            );
        }

        // Fallback for local XAMPP if direct web request had issues (e.g. self-signed SSL or localhost alias)
        if (strpos($baseUrl, 'localhost') !== false || strpos($baseUrl, '127.0.0.1') !== false) {
            $localFallback = self::_getLocalTelemetryFallback($agencyId);
            if ($localFallback['success']) {
                $localFallback['notice'] = 'Retrieved via local gateway database connection';
                return $localFallback;
            }
        }

        return array(
            'success'           => false,
            'error'             => $res['error'],
            'whatsapp_sessions' => array(),
            'sms_devices'       => array(),
            'total_sessions'    => 0,
            'total_devices'     => 0
        );
    }

    /**
     * Send WhatsApp Message via LeadBot CRM REST API (api/send.php)
     */
    public static function sendWhatsApp($gatewayUrl, $apiKey, array $params = array())
    {
        $baseUrl = self::normalizeUrl($gatewayUrl);
        $endpoint = $baseUrl . '/api/send.php';

        $recipient = self::cleanPhone(isset($params['recipient']) ? $params['recipient'] : (isset($params['phone']) ? $params['phone'] : ''));
        $message   = trim((string)(isset($params['message']) ? $params['message'] : (isset($params['text']) ? $params['text'] : '')));
        $session   = trim((string)(isset($params['session']) ? $params['session'] : ''));

        if ($recipient === '') {
            return array('success' => false, 'error' => 'Recipient phone number is required.');
        }
        if ($message === '') {
            return array('success' => false, 'error' => 'Message body cannot be empty.');
        }

        $payload = array(
            'recipient' => $recipient,
            'message'   => $message
        );
        if ($session !== '') {
            $payload['session'] = $session;
        }
        if (!empty($params['agency_id'])) {
            $payload['agency_id'] = (int)$params['agency_id'];
        }
        if (!empty($params['filename'])) {
            $payload['filename'] = trim((string)$params['filename']);
        }

        $isRemoteGateway = (strpos($baseUrl, 'localhost') === false && strpos($baseUrl, '127.0.0.1') === false);
        if (!empty($params['media_url'])) {
            $mediaUrl = trim((string)$params['media_url']);
            $mediaHost = strtolower((string)parse_url($mediaUrl, PHP_URL_HOST));
            $isLocalMedia = in_array($mediaHost, array('localhost', '127.0.0.1', '::1'), true);
            $mediaPath = (string)parse_url($mediaUrl, PHP_URL_PATH);
            $hasBinaryExt = preg_match('/\.(pdf|jpg|jpeg|png|webp|gif|mp4|mp3|ogg|wav)$/i', $mediaPath);
            $isDataUri = (strpos($mediaUrl, 'data:') === 0);

            // Only attach media_url if it is an actual binary media file reachable by the gateway server
            if ((!$isLocalMedia || !$isRemoteGateway) && ($hasBinaryExt || $isDataUri)) {
                $payload['media_url'] = $mediaUrl;
            }
        }

        $res = self::request($endpoint, $apiKey, $payload, 'POST');

        // Recovery 1: If media attachment failed to load/read on the gateway server, retry immediately as a standard text message
        if (!$res['success'] && !empty($payload['media_url'])) {
            $err = !empty($res['error']) ? $res['error'] : '';
            if (stripos($err, 'media') !== false || stripos($err, 'attachment') !== false || stripos($err, 'not be read') !== false || stripos($err, 'not be found') !== false) {
                unset($payload['media_url']);
                $retryMedia = self::request($endpoint, $apiKey, $payload, 'POST');
                if (!empty($retryMedia['success'])) {
                    $res = $retryMedia;
                }
            }
        }

        // Recovery 2: If the chosen WhatsApp profile is disconnected/inactive, auto-switch to an active connected profile
        if (!$res['success']) {
            $err = !empty($res['error']) ? $res['error'] : '';
            if (stripos($err, 'not active') !== false || stripos($err, 'disconnected') !== false || stripos($err, 'start the session') !== false) {
                $gateways = self::getGateways($gatewayUrl, $apiKey, !empty($params['agency_id']) ? $params['agency_id'] : null);
                if (!empty($gateways['whatsapp_sessions'])) {
                    foreach ($gateways['whatsapp_sessions'] as $s) {
                        $sStatus = strtoupper(!empty($s['status']) ? $s['status'] : '');
                        $sName = !empty($s['session_id']) ? $s['session_id'] : (!empty($s['name']) ? $s['name'] : '');
                        if (($sStatus === 'CONNECTED' || !empty($s['is_connected']) || !empty($s['is_online'])) && $sName !== '' && $sName !== $session) {
                            $payload['session'] = $sName;
                            unset($payload['media_url']);
                            $retrySession = self::request($endpoint, $apiKey, $payload, 'POST');
                            if (!empty($retrySession['success'])) {
                                $res = $retrySession;
                                $session = $sName;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($res['success']) {
            return array(
                'success'      => true,
                'channel'      => 'whatsapp',
                'recipient'    => $recipient,
                'session'      => $session,
                'message_id'   => isset($res['data']['message_id']) ? $res['data']['message_id'] : (isset($res['data']['id']) ? $res['data']['id'] : 'WA-' . time()),
                'status'       => 'sent',
                'response'     => $res['data'],
                'raw_response' => $res['raw'],
                'latency_ms'   => $res['latency_ms']
            );
        }

        return array(
            'success'      => false,
            'channel'      => 'whatsapp',
            'recipient'    => $recipient,
            'session'      => $session,
            'error'        => !empty($res['error']) ? $res['error'] : 'Failed to send WhatsApp message',
            'raw_response' => $res['raw'],
            'latency_ms'   => $res['latency_ms']
        );
    }

    /**
     * Send SMS Message via LeadBot Android Mobile Gateway API
     */
    public static function sendSms($gatewayUrl, $apiKey, array $params = array())
    {
        $baseUrl = self::normalizeUrl($gatewayUrl);
        $endpoint = $baseUrl . '/api/sms-gateway-api.php?action=send_test_sms';

        $recipient = self::cleanPhone(isset($params['recipient']) ? $params['recipient'] : (isset($params['phone']) ? $params['phone'] : ''));
        $message   = trim((string)(isset($params['message']) ? $params['message'] : (isset($params['text']) ? $params['text'] : '')));
        $deviceId  = !empty($params['device_id']) ? (int)$params['device_id'] : null;
        $simSlot   = !empty($params['sim_slot']) ? (int)$params['sim_slot'] : 1;

        if ($recipient === '') {
            return array('success' => false, 'error' => 'Recipient phone number is required.');
        }
        if ($message === '') {
            return array('success' => false, 'error' => 'SMS message text cannot be empty.');
        }

        $payload = array(
            'recipient' => $recipient,
            'message'   => $message,
            'sim_slot'  => $simSlot
        );
        if ($deviceId !== null && $deviceId > 0) {
            $payload['device_id'] = $deviceId;
        }
        if (!empty($params['agency_id'])) {
            $payload['agency_id'] = (int)$params['agency_id'];
        }
        if (!empty($params['filename'])) {
            $payload['filename'] = trim((string)$params['filename']);
        }

        $res = self::request($endpoint, $apiKey, $payload, 'POST');

        if ($res['success']) {
            return array(
                'success'      => true,
                'channel'      => 'sms',
                'recipient'    => $recipient,
                'device_id'    => $deviceId,
                'sim_slot'     => $simSlot,
                'sms_id'       => isset($res['data']['sms_id']) ? $res['data']['sms_id'] : (isset($res['data']['id']) ? $res['data']['id'] : 'SMS-' . time()),
                'carrier'      => isset($res['data']['carrier']) ? $res['data']['carrier'] : 'Auto-detected',
                'status'       => 'queued',
                'response'     => $res['data'],
                'raw_response' => $res['raw'],
                'latency_ms'   => $res['latency_ms']
            );
        }

        // Secondary fallback to dispatch.php
        $dispatchEndpoint = $baseUrl . '/api/dispatch.php';
        $resDispatch = self::request($dispatchEndpoint, $apiKey, array(
            'phone'    => $recipient,
            'message'  => $message,
            'channels' => array('sms'),
            'mode'     => 'direct'
        ), 'POST');

        if ($resDispatch['success']) {
            return array(
                'success'      => true,
                'channel'      => 'sms',
                'recipient'    => $recipient,
                'device_id'    => $deviceId,
                'sim_slot'     => $simSlot,
                'sms_id'       => 'DISPATCH-' . time(),
                'status'       => 'queued',
                'response'     => $resDispatch['data'],
                'raw_response' => $resDispatch['raw'],
                'latency_ms'   => $resDispatch['latency_ms']
            );
        }

        return array(
            'success'      => false,
            'channel'      => 'sms',
            'recipient'    => $recipient,
            'device_id'    => $deviceId,
            'error'        => !empty($res['error']) ? $res['error'] : (!empty($resDispatch['error']) ? $resDispatch['error'] : 'Failed to queue SMS message on gateway'),
            'raw_response' => $res['raw'],
            'latency_ms'   => $res['latency_ms']
        );
    }

    /**
     * Local direct database reader fallback for high-performance zero-overhead telemetry on XAMPP localhost
     */
    protected static function _getLocalTelemetryFallback($agencyId = null)
    {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=whtsapp;charset=utf8mb4", "root", "", array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ));

            $waSql = "SELECT ws.id, ws.session_id, ws.display_name, ws.phone_number, ws.status, ws.agency_id FROM whatsapp_sessions ws";
            $paramsW = array();
            if ($agencyId !== null && (int)$agencyId > 0) {
                $waSql .= " WHERE ws.agency_id = ? OR ws.is_agency_master = 1";
                $paramsW[] = (int)$agencyId;
            }
            $waSql .= " ORDER BY ws.id DESC";
            $stmtW = $pdo->prepare($waSql);
            $stmtW->execute($paramsW);
            $sessions = (array)$stmtW->fetchAll();

            $formattedSessions = array();
            foreach ($sessions as $s) {
                $st = strtoupper((string)$s['status']);
                $isConnected = in_array($st, array('CONNECTED', 'READY', 'ACTIVE', 'ONLINE'), true);
                $formattedSessions[] = array(
                    'id'           => (int)$s['id'],
                    'session_id'   => $s['session_id'],
                    'name'         => $s['session_id'],
                    'display_name' => !empty($s['display_name']) ? $s['display_name'] : $s['session_id'],
                    'phone_number' => !empty($s['phone_number']) ? $s['phone_number'] : '',
                    'status'       => $isConnected ? 'CONNECTED' : ($st === 'QR_READY' ? 'QR_READY' : 'DISCONNECTED'),
                    'is_online'    => $isConnected,
                    'is_connected' => $isConnected,
                    'agency_id'    => (int)$s['agency_id']
                );
            }

            $devSql = "SELECT id, device_name, device_uuid, platform, is_online, battery_level, sim_count, carrier_sim1, carrier_sim2, model_name, phone_number, agency_id FROM sms_devices WHERE is_blacklisted = 0";
            $paramsD = array();
            if ($agencyId !== null && (int)$agencyId > 0) {
                $devSql .= " AND (agency_id = ? OR 1 = 1)";
                $paramsD[] = (int)$agencyId;
            }
            $devSql .= " ORDER BY is_online DESC, id DESC";
            $stmtD = $pdo->prepare($devSql);
            $stmtD->execute($paramsD);
            $devices = (array)$stmtD->fetchAll();

            $formattedDevices = array();
            foreach ($devices as $d) {
                $formattedDevices[] = array(
                    'id'            => (int)$d['id'],
                    'device_name'   => !empty($d['device_name']) ? $d['device_name'] : 'Android Device #' . $d['id'],
                    'device_uuid'   => $d['device_uuid'],
                    'platform'      => !empty($d['platform']) ? $d['platform'] : 'android',
                    'is_online'     => (int)$d['is_online'],
                    'battery_level' => (int)$d['battery_level'],
                    'sim_count'     => (int)$d['sim_count'],
                    'carrier_sim1'  => !empty($d['carrier_sim1']) ? $d['carrier_sim1'] : 'SIM 1',
                    'carrier_sim2'  => !empty($d['carrier_sim2']) ? $d['carrier_sim2'] : 'SIM 2',
                    'model_name'    => !empty($d['model_name']) ? $d['model_name'] : '',
                    'phone_number'  => !empty($d['phone_number']) ? $d['phone_number'] : '',
                    'agency_id'     => (int)$d['agency_id']
                );
            }

            return array(
                'success'           => true,
                'latency_ms'        => 1,
                'whatsapp_sessions' => $formattedSessions,
                'sms_devices'       => $formattedDevices,
                'total_sessions'    => count($formattedSessions),
                'total_devices'     => count($formattedDevices),
                'server_time'       => date('c')
            );
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
}
