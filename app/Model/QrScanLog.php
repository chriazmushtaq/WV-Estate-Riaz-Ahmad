<?php
App::uses('AppModel', 'Model');

class QrScanLog extends AppModel
{
    public $useTable = 'qr_scan_logs';
    public $primaryKey = 'id';
    public $order = array('QrScanLog.id' => 'DESC');

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->_ensureTableSchema();
    }

    protected function _ensureTableSchema()
    {
        try {
            $db = $this->getDataSource();
            $db->execute("CREATE TABLE IF NOT EXISTS `qr_scan_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `letter_id` int(11) DEFAULT 0,
              `reference_no` varchar(100) DEFAULT NULL,
              `deal_id` int(11) DEFAULT 0,
              `client_id` int(11) DEFAULT 0,
              `client_name` varchar(191) DEFAULT NULL,
              `property_name` varchar(191) DEFAULT NULL,
              `token_attempted` varchar(100) DEFAULT NULL,
              `scan_status` varchar(30) NOT NULL DEFAULT 'VERIFIED',
              `is_suspicious` tinyint(1) NOT NULL DEFAULT 0,
              `suspicious_reason` varchar(255) DEFAULT NULL,
              `ip_address` varchar(64) DEFAULT NULL,
              `city` varchar(100) DEFAULT NULL,
              `country` varchar(100) DEFAULT NULL,
              `user_agent` text DEFAULT NULL,
              `device_type` varchar(50) DEFAULT 'Desktop',
              `browser` varchar(50) DEFAULT NULL,
              `os` varchar(50) DEFAULT NULL,
              `scan_url` varchar(255) DEFAULT NULL,
              `created` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_letter_id` (`letter_id`),
              KEY `idx_scan_status` (`scan_status`),
              KEY `idx_is_suspicious` (`is_suspicious`),
              KEY `idx_ip_address` (`ip_address`),
              KEY `idx_created` (`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        } catch (Exception $e) {
            // Silently proceed if db user lacks DDL or table already exists
        }
    }

    /**
     * Parse client IP supporting reverse proxy / Cloudflare
     */
    public function getClientIp()
    {
        $headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        );
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $raw = trim((string)$_SERVER[$header]);
                if (strpos($raw, ',') !== false) {
                    $parts = explode(',', $raw);
                    $raw = trim($parts[0]);
                }
                if (filter_var($raw, FILTER_VALIDATE_IP)) {
                    return $raw;
                }
            }
        }
        return '127.0.0.1';
    }

    /**
     * Parse user agent for device, browser, and OS
     */
    public function parseUserAgent($ua = null)
    {
        if ($ua === null) {
            $ua = !empty($_SERVER['HTTP_USER_AGENT']) ? (string)$_SERVER['HTTP_USER_AGENT'] : '';
        }

        $device = 'Desktop';
        $browser = 'Unknown';
        $os = 'Unknown';

        // Device
        if (preg_match('/(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk)/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(mobi|ipod|iphone|android|blackberry|webos|windows phone)/i', $ua)) {
            $device = 'Mobile';
        }

        // OS
        if (preg_match('/windows nt 10/i', $ua)) { $os = 'Windows 10/11'; }
        elseif (preg_match('/windows nt 6\.3/i', $ua)) { $os = 'Windows 8.1'; }
        elseif (preg_match('/windows nt 6\.2/i', $ua)) { $os = 'Windows 8'; }
        elseif (preg_match('/windows nt 6\.1/i', $ua)) { $os = 'Windows 7'; }
        elseif (preg_match('/macintosh|mac os x/i', $ua)) { $os = 'macOS'; }
        elseif (preg_match('/iphone/i', $ua)) { $os = 'iOS (iPhone)'; }
        elseif (preg_match('/ipad/i', $ua)) { $os = 'iOS (iPad)'; }
        elseif (preg_match('/android/i', $ua)) { $os = 'Android'; }
        elseif (preg_match('/linux/i', $ua)) { $os = 'Linux'; }

        // Browser
        if (preg_match('/edg/i', $ua)) { $browser = 'Edge'; }
        elseif (preg_match('/chrome/i', $ua) && !preg_match('/edg/i', $ua)) { $browser = 'Chrome'; }
        elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) { $browser = 'Safari'; }
        elseif (preg_match('/firefox/i', $ua)) { $browser = 'Firefox'; }
        elseif (preg_match('/opera|opr/i', $ua)) { $browser = 'Opera'; }
        elseif (preg_match('/trident/i', $ua)) { $browser = 'IE'; }

        return array(
            'device_type' => $device,
            'browser' => $browser,
            'os' => $os
        );
    }

    /**
     * Resolve approximate City / Country from IP
     */
    public function resolveLocation($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            return array('city' => 'Local Network', 'country' => 'Localhost');
        }

        // Fast IP geolocation fallback with timeout
        try {
            $ctx = stream_context_create(array('http' => array('timeout' => 1.5)));
            $json = @file_get_contents('http://ip-api.com/json/' . urlencode($ip) . '?fields=status,city,country', false, $ctx);
            if ($json) {
                $data = json_decode($json, true);
                if (!empty($data['status']) && $data['status'] === 'success') {
                    return array(
                        'city' => !empty($data['city']) ? $data['city'] : 'Unknown',
                        'country' => !empty($data['country']) ? $data['country'] : 'Pakistan'
                    );
                }
            }
        } catch (Exception $e) {}

        return array('city' => 'Pakistan Area', 'country' => 'Pakistan');
    }

    /**
     * Log a QR Scan with Security Audit
     */
    public function logScan($letterId, $tokenAttempted, $status, $meta = array())
    {
        $ip = $this->getClientIp();
        $ua = !empty($_SERVER['HTTP_USER_AGENT']) ? (string)$_SERVER['HTTP_USER_AGENT'] : '';
        $parsedUa = $this->parseUserAgent($ua);
        $loc = $this->resolveLocation($ip);

        $isSuspicious = 0;
        $suspiciousReason = '';

        // Check if multiple invalid attempts occurred from this IP or letter
        if ($status !== 'VERIFIED') {
            $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
            $failedCount = $this->find('count', array(
                'conditions' => array(
                    'QrScanLog.ip_address' => $ip,
                    'QrScanLog.scan_status !=' => 'VERIFIED',
                    'QrScanLog.created >=' => $oneHourAgo
                ),
                'recursive' => -1
            ));

            if ($failedCount >= 2) {
                $isSuspicious = 1;
                $suspiciousReason = 'Repeated invalid scan attempts (' . ($failedCount + 1) . ') detected from IP ' . $ip . ' in the last hour.';
            } elseif ($status === 'INVALID_TOKEN') {
                $suspiciousReason = 'Unrecognized / altered SHA-256 cryptographic security token scanned.';
            } elseif ($status === 'NOT_FOUND') {
                $suspiciousReason = 'Document reference ID does not exist in registry.';
            }
        }

        $record = array(
            'letter_id' => !empty($letterId) ? (int)$letterId : 0,
            'reference_no' => !empty($meta['reference_no']) ? $meta['reference_no'] : null,
            'deal_id' => !empty($meta['deal_id']) ? (int)$meta['deal_id'] : 0,
            'client_id' => !empty($meta['client_id']) ? (int)$meta['client_id'] : 0,
            'client_name' => !empty($meta['client_name']) ? $meta['client_name'] : null,
            'property_name' => !empty($meta['property_name']) ? $meta['property_name'] : null,
            'token_attempted' => !empty($tokenAttempted) ? substr($tokenAttempted, 0, 100) : '',
            'scan_status' => $status,
            'is_suspicious' => $isSuspicious,
            'suspicious_reason' => $suspiciousReason,
            'ip_address' => $ip,
            'city' => !empty($loc['city']) ? $loc['city'] : '',
            'country' => !empty($loc['country']) ? $loc['country'] : '',
            'user_agent' => substr($ua, 0, 1000),
            'device_type' => $parsedUa['device_type'],
            'browser' => $parsedUa['browser'],
            'os' => $parsedUa['os'],
            'scan_url' => !empty($_SERVER['REQUEST_URI']) ? substr($_SERVER['REQUEST_URI'], 0, 255) : '',
            'created' => date('Y-m-d H:i:s')
        );

        $this->create();
        return $this->save($record);
    }
}
